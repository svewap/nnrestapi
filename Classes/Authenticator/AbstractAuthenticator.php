<?php

namespace Nng\Nnrestapi\Authenticator;

use Nng\Nnrestapi\Service\TokenService;

use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Session\UserSession;

abstract class AbstractAuthenticator {

	/**
	 * Called by Authenticator Middleware.
	 * Please implement this method in your custom authenticator.
	 *  
	 * The Middleware expects one of the following return values:
	 * 
	 * `false`							=>	Authentication failed. 
	 * 										The Middleware will continue with the next registered 
	 * 										Authenticator.
	 * 
	 * `['feUserSessionId'=>'....']`	=> 	Authentication successful. 
	 * 										Array with the `ses_id` of the current frontend user.
	 * 										Typically stored in `fe_sessions.ses_id`. The Middleware
	 * 										will set the `fe_typo_user`-Cookie and the TYPO3 Core will
	 * 										take care of logging in the FrontendUser.
	 * 
	 * `true`							=>	Authentication successful.
	 * 										Some custom logic, no `fe_typo_user`-Cookie will be set.
	 * 										Typo3 will not login any FrontendUser. The Middleware will
	 * 										not proceed with the next Authenticator.
	 * 
	 * @return mixed
	 */
	public function process( $request = null ) {
		\nn\t3::Exception( 'Your custom nnrest-Authenticator must have a process() method!' );
	}


	/**
	 * Start a FrontenUser-Session by a `fe_users.uid` or `fe_users.username`.
	 * Return the Session-ID for the user.
	 * ```
	 * $sessionId = $this->createFeUserSession( 1 );
	 * $sessionId = $this->createFeUserSession( 'david' );
	 * ```
	 * @return UserSession
	 */
	public function createFeUserSession( $usernameOrUid = null ) {

		if (!$usernameOrUid) return null;

		if ($uid = intval($usernameOrUid)) {
			$user = \nn\t3::Db()->findByUid('fe_users', $uid);
		} else {
			$user = \nn\t3::Db()->findOneByValues('fe_users', ['username'=>$usernameOrUid]);
		}

		if (!$user) return null;

		// ToDo: Find a way to restart an existing session!
		
		$frontendUser = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
		$frontendUser->start();
		$session = $frontendUser->createUserSession( $user );
		
		if (!$session) return null;

		return $session;
	}

	/**
	 * Login a FrontendUser and return SessionID.
	 * Just for testing purposes.
	 * 
	 * @return string
	 */
	public function simulateUserSession() {
		return $this->createFeUserSession( 1 );
	}
}