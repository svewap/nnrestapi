<?php 

namespace Nng\Nnrestapi\Utilities;

use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper for managing api-Sessions and Tokens.
 * 
 */
class Session extends \Nng\Nnhelpers\Singleton {

	/**
	 * Tablename that stores the session-data
	 * 
	 */
	const TABLENAME = 'nnrestapi_sessions';

	/**
	 * Get the session-data for a given JWT from the `nnrestapi_sessions` table.
	 * We are __not__ using the standard Model/Repository methods here to increase performance.
     * ```
	 * $session = \nn\rest::Session()->get( $token );
	 * ```
	 * @return array
	 */
	public function get( $token = null ) 
	{
		$hashedToken = \nn\t3::Encrypt()->hash( $token );

		$data = \nn\t3::Db()->findOneByValues( self::TABLENAME, ['token'=>$hashedToken] );
		if (!$data) return [];
		
		$data['data'] = \nn\t3::Encrypt()->decode( $data['data'] );
		return $data;
	}
	
	/**
	 * Create (or update) a session-data-entry for a given JWT in the table `nnrestapi_sessions`.
	 * The data passed as second parameter will be encrypted in the database.
	 * We are __not__ using the standard Model/Repository methods here to increase performance.
     * ```
	 * \nn\rest::Session()->update( $identifier, ['sid'=>'...'] );
	 * ```
	 * @return array
	 */
	public function update( $token = null, $data = [] ) 
	{
		// create a hashed identifer. For security reasons no plaintext tokens are stored in DB.
		$hashedToken = \nn\t3::Encrypt()->hash( $token );

		$data = [
			'data' 		=> \nn\t3::Encrypt()->encode( $data ),
			'tstamp' 	=> time(),
			'token'		=> $hashedToken,
		];
		if ($entry = $this->get($token)) {
			return \nn\t3::Db()->update( self::TABLENAME, $data, $entry['uid'] );
		}
		return \nn\t3::Db()->insert( self::TABLENAME, $data );
	}
	
	/**
	 * Alias to `update()`
	 * ```
	 * \nn\rest::Session()->create( $identifier, ['sid'=>'...'] );
	 * ```
	 * @return array
	 */
	public function create( $token = null, $data = [] ) {
		return $this->update( $token, $data );
	}
	
	/**
	 * Destroys a session
	 * ```
	 * \nn\rest::Session()->destroy();
	 * \nn\rest::Session()->destroy( $identifier );
	 * ```
	 * @return array
	 */
	public function destroy( $token = null ) 
	{
		if (!$token) {
			$jwt = \nn\t3::Request()->getJwt() ?: [];
			$token = $jwt['token'] ?? false;
		}
		if ($token) {
			$hashedToken = \nn\t3::Encrypt()->hash( $token );
			\nn\t3::Db()->delete( self::TABLENAME, ['token'=>$hashedToken] );
		}
	}

	/**
	 * Update the current tstamp of a session.
     * ```
	 * \nn\rest::Session()->touch( $jwt );
	 * ```
	 * @return array
	 */
	public function touch( $token = null ) 
	{
		$data = [
			'tstamp' => time(),
		];
		if ($entry = $this->get($token)) {
			return \nn\t3::Db()->update( self::TABLENAME, $data, $entry['uid'] );
		}
		return false;
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
	 * \nn\rest::Session()->removeExpiredTokens();
	 * ```
	 * @return void
	 */
	public function removeExpiredTokens() 
	{
		$maxSessionLifetime = \nn\t3::Settings()->getExtConf('nnrestapi')['maxSessionLifetime'] ?? 0;
		if ($maxSessionLifetime == 0) return;
		
		$queryBuilder = \nn\t3::DB()->getQueryBuilder( self::TABLENAME );
		$queryBuilder->delete( self::TABLENAME );
		$queryBuilder->andWhere(
			$queryBuilder->expr()->lt( 'tstamp', $queryBuilder->createNamedParameter(time() - $maxSessionLifetime))
		);

		// If required tables for nnrestapi have not been installed yet, stay silent.
		try {
			$queryBuilder->execute();
		} catch( \Throwable $e ) {
		} catch ( \Exception $e ) {}
	}

	/**
	 * Start a FeUser-session.
	 * 
	 * Checks, if there is still a valid session in the table `fe_sessions` for the given user-uid and
	 * sessionId. If not, it will automatically create a new FE-user-session. 
	 * 
	 * If the session was successfully created, the method will automatically set Frontend-User-Cookie 
	 * in `$_COOKIE['fe_typo_user']` and in the `Request`-object. As the `nnrestapi` Authenticator MiddleWare
	 * is called __before__ the standard `typo3/cms-frontend/authentication`, the Core Typo3 Frontend-
	 * Authenticator will be fooled in thinking it was sent by the browser.
	 * 
	 * Returns the `sessionId` (unhashed) for the new (or current) FE-User. 
	 * 
	 * Params:
	 * 
	 * `sessionIdentifier`	=> 	A random, unique identifier to identify the user-session.
	 * 							Will be stored as hash in `nnrestapi_sessions.token`
	 * 
	 * `feUser`				=>	The `uid` of the frontend-user - or the username
	 * 
	 * `request`			=>	The Request-Object. Needed to set the cookieParams as
	 * 							`typo3/cms-frontend/authentication` will read the `fe_typo_user`-cookie
	 * 							from the `Request` and not from the global `$_COOKIE` variable.
	 * 
	 * Example:
	 * ```
	 * \nn\t3::Session()->start( $sessionIdentifier, $feUserUid, $request );
	 * ```
	 * @return string
	 */
	public function start( $sessionIdentifier = '', $feUser = '', &$request = '' ) {
		
		// no feUser-uid passed? Abort.
		if (!trim($feUser)) return false;
		
		// get session-data for token from table `nnrestapi_sessions`. If expired or invalid: Abort.
		$session = $this->get( $sessionIdentifier );
		if (!$session) return false;

		// get the original, unhashed `fe_typo_user`-sessionId stored in `nnrestapi_sessions.data`
		$oldSessionId = $session['data']['sid'] ?? false;
		if (!$oldSessionId) return false;
		
		// create (or recreate) session using identical sessionId from previous session
		$sessionId = \nn\t3::FrontendUserAuthentication()->prepareSession( $feUser, $oldSessionId );

		// Aboort, if no session could be (re)created
		if (!$sessionId) return false;

		// Override `fe_typo_user`-Cookie in `$_COOKIE` and in the current `Request`
		\nn\t3::FrontendUser()->setCookie( $sessionId, $request );

		return $sessionId;
	}


}