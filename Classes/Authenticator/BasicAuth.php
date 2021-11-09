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
 * - in the TCA of the individual `fe_user.nnrestapi_apikey`-entries
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

		// Make sure, the username really exists in DB before continuing
		$feUser = \nn\t3::Db()->findOneByValues('fe_users', [
			'username' => $username
		]);

		// `fe_user` doesn't exist or is disabled? Abort.
		if (!$feUser) return false;

		// Get users defined in the Extension Manager
		$userlist = \nn\t3::Arrays( \nn\t3::Environment()->getExtConf('nnrestapi', 'apiKeys') )->trimExplode("\n");
		$userlistByAuth = array_combine( $userlist, $userlist );
		
		$user = $userlistByAuth["{$username}:{$apiKey}"] ?? false;

		// User not found in EXT-Configuration AND apiKey not correct in `fe_user.nnrestapi_apikey`
		if (!$user && $feUser['nnrestapi_apikey'] != $apiKey) {
			return false;
		}

		return $feUser;
	}

}