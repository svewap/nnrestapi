<?php

namespace Nng\Nnrestapi\Authenticator;

abstract class AbstractAuthenticator {

	/**
	 * Called by Authenticator Hook (`Hooks/FrontendUserAuthenticationHook`).
	 * Please implement this method in your custom authenticator.
	 *  
	 * The Hook expects one of the following return values:
	 * 
	 * `false`	=>	Authentication failed. 
	 * 				The Hook will continue with the next registered Authenticator.
	 *  
	 * [user]	=>	Array with`fe_user`-data if authentication was successful.
	 * 				The Hook will stop calling any further Authenticators.
	 * 
	 * @return mixed
	 */
	public function process() {
		\nn\t3::Exception( 'Your custom nnrest-Authenticator must have a process() method!' );
	}

}