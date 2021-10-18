<?php

namespace Nng\Nnrestapi\Authenticator;

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
	 * `false`	=>	Authentication failed. 
	 * 				The Middleware will continue with the next registered Authenticator.
	 *  
	 * `true`	=>	Authentication successful.
	 * 				The MiddleWare will stop calling any further Authenticators.
	 * 
	 * @return mixed
	 */
	public function process( &$request = null ) {
		\nn\t3::Exception( 'Your custom nnrest-Authenticator must have a process() method!' );
	}

}