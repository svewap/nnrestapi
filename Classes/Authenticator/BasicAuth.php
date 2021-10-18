<?php

namespace Nng\Nnrestapi\Authenticator;

class BasicAuth extends AbstractAuthenticator {

	/**
	 * Called by Authenticator Middleware.
	 * 
	 * Checks, if a Basic-AUTH-Header was passed and if the API-Key was registered
	 * in the backend extension-configuration
	 * 
	 * If the basicAuth does not work, you might need to add these two lines to
	 * your `.htaccess` directly after the `RewriteEngine on` command:
	 * 
	 * ```
	 * RewriteCond %{HTTP:Authorization} ^(.*)
	 * RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
	 * ```
	 * 
	 * See AbstractAuthenticator for more details
	 * 
	 * @return mixed
	 */
	public function process( &$request = null ) {

		$credentials = \nn\t3::Request()->getBasicAuth();
		if (!$credentials) return false;

		$username = $credentials['username'];
		$password = $credentials['password'];

		// Abort, if the default user from the Extension Manager was passed
		if ($username == 'examplefeUserName') {
			return false;
		}

		// Get users defined in the Extension Manager
		$userlist = \nn\t3::Arrays( \nn\t3::Environment()->getExtConf('nnrestapi', 'apiKeys') )->trimExplode("\n");
		$userlistByAuth = array_combine( $userlist, $userlist );

		if ($userlistByAuth["{$username}:{$password}"] ?? false) {

			die('SOME LOGIC... to do');
			if ($session = $this->createFeUserSession( $username )) {
				return ['feUserSessionId' => $session->getIdentifier()];
			}
		}

		return false;
	}

}