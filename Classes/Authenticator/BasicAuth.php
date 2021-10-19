<?php

namespace Nng\Nnrestapi\Authenticator;

/**
 * # BasicAuth
 * 
 * Authenticator for logging in a user by `username` / `apiKey` credentials.
 * Checks, if a Basic-AUTH-Header was passed and if the API-Key is valid.
 * 
 * The ApiKey can be defined ... 
 * - in the backend EXT-Manager. One {fe-username}:{apiKey} per line
 * - in the TCA of the individual `fe_user`-entries
 * 
 * You can use this simple script to test the authentication from outside of the installation:
 * ```
 * <?php
 * 
 * $result = file_get_contents('https://username:apikey@www.yourserver.com/api/user');
 * print_r( json_decode($result, true) );
 * ```
 * 
 * __Important note:__ If the basicAuth does not work, you might need to add these two lines to
 * your `.htaccess` directly after the `RewriteEngine on` command:
 * 
 * ```
 * RewriteCond %{HTTP:Authorization} ^(.*)
 * RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
 * ```
 * 
 */
class BasicAuth extends AbstractAuthenticator {

	/**
	 * Called by Authenticator Middleware.
	 * 
	 * @return mixed
	 */
	public function process( &$request = null ) {

		$credentials = \nn\t3::Request()->getBasicAuth();
		if (!$credentials) return false;

		$username = $credentials['username'];
		$apiKey = $credentials['password'];

		// Abort, if the default user from the Extension Manager was passed
		if ($username == 'examplefeUserName') {
			return false;
		}

		// Get users defined in the Extension Manager
		$userlist = \nn\t3::Arrays( \nn\t3::Environment()->getExtConf('nnrestapi', 'apiKeys') )->trimExplode("\n");
		$userlistByAuth = array_combine( $userlist, $userlist );
		$user = $userlistByAuth["{$username}:{$apiKey}"] ?? false;

		// User not found in EXT-Configuration?
		if (!$user) {

			// ... then check for credentials in `fe_user`-table
			if ($feUser = \nn\t3::Db()->findOneByValues('fe_users', [
				'username'			=> $username, 
				'nnrestapi_apikey'	=> $apiKey
			])) {
				$user = true;
				$username = $feUser['uid'];
			}
		}

		// No user found? Abort!
		if (!$user) return false;

		// Use the username:ApiKey as a session identifer (it will be hashed in the database)
		$sessionIdentifier = "{$username}.{$apiKey}";

		// (Re)start current session or create a new one. `true` as last parameter will allow to auto-create a new session
		$sessionId = \nn\rest::Session()->start( $sessionIdentifier, $username, $request, true );

		// something went wrong. Destroy session.
		if (!$sessionId) return false;

		return true;

	}

}