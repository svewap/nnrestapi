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
	 * $session = \nn\rest::Session()->get( $jwt );
	 * ```
	 * @return array
	 */
	public function get( $jwt = null ) 
	{
		$data = \nn\t3::Db()->findOneByValues( self::TABLENAME, ['token'=>$jwt] );
		if (!$data) return [];
		
		$data['data'] = \nn\t3::Encrypt()->decode( $data['data'] );
		return $data;
	}
	
	/**
	 * Create (or update) a session-data-entry for a given JWT in the table `nnrestapi_sessions`.
	 * The data passed as second parameter will be encrypted in the database.
	 * We are __not__ using the standard Model/Repository methods here to increase performance.
     * ```
	 * \nn\rest::Session()->update( $jwt, ['sid'=>'...'] );
	 * ```
	 * @return array
	 */
	public function update( $jwt = null, $data = [] ) 
	{
		$data = [
			'data' 		=> \nn\t3::Encrypt()->encode( $data ),
			'tstamp' 	=> time(),
			'token'		=> $jwt,
		];
		if ($entry = $this->get($jwt)) {
			return \nn\t3::Db()->update( self::TABLENAME, $data, $entry['uid'] );
		}
		return \nn\t3::Db()->insert( self::TABLENAME, $data );
	}
	
	/**
	 * Alias to `update()`
	 * ```
	 * \nn\rest::Session()->create( $jwt, ['sid'=>'...'] );
	 * ```
	 * @return array
	 */
	public function create( $jwt = null, $data = [] ) {
		return $this->update( $jwt, $data );
	}

	/**
	 * Update the current tstamp of a session.
     * ```
	 * \nn\rest::Session()->touch( $jwt );
	 * ```
	 * @return array
	 */
	public function touch( $jwt = null ) 
	{
		$data = [
			'tstamp' => time(),
		];
		if ($entry = $this->get($jwt)) {
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
     * ```
	 * \nn\rest::Session()->removeExpiredTokens();
	 * ```
	 * @return array
	 */
	public function removeExpiredTokens() 
	{
		$maxSessionLifetime = \nn\t3::Settings()->get('nnrestapi')['maxSessionLifetime'];
		if ($maxSessionLifetime == 0) return;

		$queryBuilder = \nn\t3::DB()->getQueryBuilder( self::TABLENAME );
		$queryBuilder->delete( self::TABLENAME );
		$queryBuilder->andWhere(
			$queryBuilder->expr()->lt( 'tstamp', $queryBuilder->createNamedParameter(time() - $maxSessionLifetime))
		);
		return $queryBuilder->execute();
	}

	/**
	 * (Re)start a FeUser-session.
	 * 
	 * Checks, if there is still a valid session in the table `fe_sessions` for the given user-uid and
	 * sessionId. If not, it will automatically create a new FE-user-session. Returns the `sessionId` 
	 * (not hashed) for the new (or current) FE-User. This sessionId will be used to set the 
	 * Frontend-User-Cookie in `$_COOKIE['fe_typo_user']`.
	 * 
	 * ```
	 * \nn\t3::Session()->restart( $feUserUid, $sessionId );
	 * ```
	 * @return string
	 */
	public function restart( $feUserUid = '', $sessionId = '' ) {
		if (!$feUserUid) return;

		$hashedSessionId = \nn\t3::Encrypt()->hashSessionId( $sessionId );
		$session = \nn\t3::Db()->findOneByValues( 'fe_sessions', ['ses_id'=>$hashedSessionId, 'ses_userid'=>$feUserUid] );

		// Session is still valid. Return current sessionId
		if ($session) return $sessionId;

		// Session has expired. Create a new one and return new sessionId
		return \nn\t3::FrontendUserAuthentication()->prepareSession( $feUserUid );
	}

}