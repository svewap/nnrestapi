<?php

namespace Nng\Nnrestapi\Authenticator;

use Nng\Nnrestapi\Service\TokenService;

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
	public function process( $request = null ) {

		$username = null;
		$password = null;

		if (isset($_SERVER['PHP_AUTH_USER'])) {
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
        } elseif ($authData = $this->checkServerAuthData('HTTP_AUTHENTICATION')) {
            [$username, $password] = $authData;
        } elseif ($authData = $this->checkServerAuthData('HTTP_AUTHORIZATION')) {
            [$username, $password] = $authData;
        } elseif ($authData = $this->checkServerAuthData('REDIRECT_HTTP_AUTHORIZATION')) {
            [$username, $password] = $authData;
        }

		if (!$username || !$password) {
			return false;
		}

		// Abort, if the default user from the Extension Manager was passed
		if ($username == 'examplefeUserName') {
			return false;
		}

		// Get users defined in the Extension Manager
		$userlist = \nn\t3::Arrays( \nn\t3::Environment()->getExtConf('nnrestapi', 'apiKeys') )->trimExplode("\n");
		$userlistByAuth = array_combine( $userlist, $userlist );

		if ($userlistByAuth["{$username}:{$password}"] ?? false) {
			if ($session = $this->createFeUserSession( $username )) {
				return ['feUserSessionId' => $session->getIdentifier()];
			}
		}

		return false;
	}

	/**
	 * Check for Server Authorization data.
	 * 
	 * @return array
	 */
	private function checkServerAuthData($key) {
        if ($value = $_SERVER[$key] ?? false) {
            if (strpos(strtolower($value), 'basic') === 0) {
                return explode(':', base64_decode(substr($value, 6)));
            }
        }
        return [];
    }

}