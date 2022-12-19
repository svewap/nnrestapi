<?php 

namespace Nng\Nnrestapi\Utilities;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Context\Context;

/**
 * Helper for performing basic security checks
 * 
 */
class Security extends \Nng\Nnhelpers\Singleton 
{
	/**
	 * Tablename that stores the security-data
	 * 
	 * @var string
	 */
	const TABLENAME = 'nnrestapi_security';

	/**
	 * Cache for current request
	 * 
	 * @var array
	 */
	var $request = [];

	/**
	 * Cache for logs from DB for current IP, 
	 * grouped by column `identifier`
	 * 
	 * @var array
	 */
	var $logs = null;

	/**
	 * IP (hashed) of current user.
	 * We never will store any legible IP in the database
	 * 
	 * @var string
	 */
	var $hashedIp = null;
	
	/**
	 * Fe-User UID of current user.
	 * 
	 * @var string
	 */
	var $feUserUid = null;

	/**
	 * Injection patterns
	 * 
	 * @var array
	 */
	var $injectionPatterns = [
		"'\s*(;\s*)?--(\s|')",
		"'\s*(and|or|xor|&&|\|\|)\s*\(?\s*('|[0-9]|`?[a-z\._-]+`?\s*(=|like)|[a-z]+\s*\()",
		"'\s*(not\s+)?in\s*\(\s*['0-9]",
		"union(\s+all)?(\s*\(\s*|\s+)select(`|\s)",
		"select(\s*`|\s+)(\*|[a-z0-9_\, ]*)(`\s*|\s+)from(\s*`|\s+)[a-z0-9_\.]*",
		"insert\s+into(\s*`|\s+).*(`\s*|\s+)(values\s*)?\(.*\)",
		"update(\s*`|\s+)[a-z0-9_\.]*(`\s*|\s+)set(\s*`|\s+).*=",
		"delete\s+from(\s*`|\s+)[a-z0-9_\.]*`?",
		"sleep([\s]*)\([0-9\s]*\)",
		"(\"|\')([\+;|\s]*);([\+;|\s]*)(union|select|delete|insert|or|alter|drop|and)(\s|\+)",
		"(((\+)|(\ ))(((\%27)|(\'))|union|select|delete|insert|or|alter|drop|and)(((\+)|(\ ))))",
	];

	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct( $request = null ) 
	{
		$this->request = $request;
		$remoteAddr = $request ? $request->getRemoteAddr() : $_SERVER['REMOTE_ADDR']; 
		$this->hashedIp = crc32(md5($remoteAddr));

		$context = GeneralUtility::makeInstance(Context::class);
		$userAspect = $context->getAspect('frontend.user');
		$this->feUserUid = $userAspect ? $userAspect->get('id') : 0;
	}

	/**
	 * Load logs (and cache) for current IP.
	 * 
	 * @return array
	 */
	private function init() 
	{
		if ($this->logs === null) {

			$this->removeExpiredEntries();

			$constraints = ['iphash'=>$this->hashedIp];
			if ($feUserUid = $this->feUserUid) {
				$constraints['feuser'] = $feUserUid;
			}

			$logs = \nn\t3::Db()->findByValues( self::TABLENAME, $constraints, false );
			$this->logs['all'] = $logs;

			// group logfiles by the type
			foreach ($logs as $row) {
				$identifier = $row['identifier'];
				if (!isset($this->logs[$identifier])) {
					$this->logs[$identifier] = [];
				}
				$this->logs[$identifier][] = $row;
			}
		}
	}
	
	/**
	 * Checks if the number of requests from current IP exceeds the
	 * limits set in Annotation `@Api\Security\MaxRequestsPerMinute(5, "myID")`
	 * 
	 * ```
	 * \nn\rest::Security( $request )->maxRequestsPerMinute(['all'=>60]);
	 * ```
	 * @param array $limits
	 * @return boolean
	 */
	public function maxRequestsPerMinute( $limits = [] ) 
	{
		$this->init();

		foreach ($limits as $identifier=>$max) {
			
			$rows = $this->logs[$identifier] ?? [];
			
			if (count($rows) >= $max) {
				return false;
			}

			$this->log( $identifier, 60 );
		}

		return true;
	}
	
	/**
	 * Checks if the current IP was blacklisted
	 * `@Api\Security\CheckLocked()`
	 * 
	 * ```
	 * \nn\rest::Security( $request )->checkLocked();
	 * ```
	 * @param array $limits
	 * @return boolean
	 */
	public function checkLocked( $params = null ) 
	{
		$this->init();
		return 
			count($this->logs['lock'] ?? []) == 0 &&
			count($this->logs['feuser'] ?? []) == 0;
	}
	
	
	/**
	 * Checks for typical SQL-injection patterns in the request.
	 * 
	 * At this point you probably will laught, but we have been
	 * using a similar method for double-security in various installations.
	 * You would be shocked, how often this has been a successfull
	 * doorman.
	 * 
	 * ```
	 * @Api\Security\CheckInjections()
	 * @Api\Security\CheckInjections(false)
	 * 
	 * \nn\rest::Security( $request )->checkInjections();
	 * ```
	 * @param array $limits
	 * @return boolean
	 */
	public function checkInjections( $autoLock = null ) 
	{
		if ($autoLock !== false) {
			$autoLock = true;
		}

		// merge request to string
		$str = json_encode($_GET ?? []);
		$str .= $this->request ? $this->request->getRawBody() : '';

		// remove comments
		$str = preg_replace('!/\*.*?\*/!s', '', $str);
		$str = preg_replace('/\n\s*\n/', "\n", $str);

		$str = stripslashes( $str );

		$found = false;

		foreach ($this->injectionPatterns as $pattern) {
			if (preg_match("/{$pattern}/i", $str)) {
				$found = true;
				break;
			}
		}

		if (!$found) return true;

		if ($autoLock) {
			$this->lockIp( 86400, addslashes($str) );
		}

		return false;
	}

	/**
	 * Insert an entry in the log-table
	 * 
	 * @param array $data
	 * @return array
	 */
	public function log( $identifier = '', $expires = 0, $data = '' ) 
	{
		$row = [
			'identifier' => $identifier,
			'iphash' 	=> $this->hashedIp,
			'feuser' 	=> $this->feUserUid,
			'tstamp' 	=> time(),
			'expires' 	=> time() + $expires,
			'data' 		=> $data,
		];
		\nn\t3::Db()->insert( self::TABLENAME, $row);

		if (!isset($this->logs[$identifier])) {
			$this->logs[$identifier] = [];
		}
		$this->logs[$identifier][] = $row;
	}

	/**
	 * Blacklist an IP.
	 * Locks all requests from current IP.
	 * 
	 * ```
	 * \nn\rest::Security()->lockIp();
	 * ```
	 * 
	 * @param array $data
	 * @return array
	 */
	public function lockIp( $expires = 86400, $data = '' ) 
	{
		$this->init();
		$this->log( 'lock', $expires, $data );
	}
	
	/**
	 * Unlock an IP after it has been locked.
	 * 
	 * ```
	 * \nn\rest::Security()->unlockIp();
	 * ```
	 * 
	 * @param array $data
	 * @return array
	 */
	public function unlockIp() 
	{
		\nn\t3::Db()->delete( self::TABLENAME, ['iphash' => $this->hashedIp], true );
	}
	
	/**
	 * Blacklist an User.
	 * Locks all requests from current feUser.
	 * 
	 * ```
	 * \nn\rest::Security()->lockFeUser();
	 * \nn\rest::Security()->lockFeUser( 1, 86400 );
	 * ```
	 * 
	 * @param array $data
	 * @return array
	 */
	public function lockFeUser( $userUid = null, $expires = 86400 ) 
	{
		$this->init();
		if ($userUid !== null) {
			$this->feUserUid = $userUid;
		}
		$this->log( 'feuser', $expires, $data );
	}
	
	/**
	 * Unlock an IP after it has been locked.
	 * 
	 * ```
	 * \nn\rest::Security()->unlockFeUser( $userUid );
	 * ```
	 * 
	 * @param array $data
	 * @return array
	 */
	public function unlockFeUser( $userUid = null ) 
	{
		\nn\t3::Db()->delete( self::TABLENAME, ['feuser' => $userUid ?: $this->feUserUid], true );
		$this->unlockIp();
	}

	/**
	 * Remove expired sessions from database.
	 * 
	 * The expiration of the session can be set in the TypoScript-Setup of `nnrestapi`
	 * under `plugin.tx_nnrestapi.settings.maxSessionLifetime`. Setting this value to `0`
	 * will create permanent sessions that never expire.
	 * 
	 * This method will throw a 503 error in the frontend, if the required database-tables
	 * for this extension are missing. The error is surpressed, but there will be a warning
	 * in the nnrestapi backend module.
	 * 
     * ```
	 * \nn\rest::Security()->removeExpiredEntries();
	 * ```
	 * @return void
	 */
	public function removeExpiredEntries() 
	{		
		$queryBuilder = \nn\t3::DB()->getQueryBuilder( self::TABLENAME );
		$queryBuilder->delete( self::TABLENAME );
		$queryBuilder->andWhere(
			$queryBuilder->expr()->lt( 'expires', $queryBuilder->createNamedParameter(time()))
		);

		// If required tables for nnrestapi have not been installed yet, stay silent.
		try {
			$queryBuilder->executeStatement();
		} catch( \Throwable $e ) {
		} catch ( \Exception $e ) {}
	}
	
}