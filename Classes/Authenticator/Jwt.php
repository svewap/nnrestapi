<?php

namespace Nng\Nnrestapi\Authenticator;

use Nng\Nnrestapi\Service\TokenService;

/**
 * Bereitet Login eines fe_users anhand seines JWT vor.
 * 
 * Ablauf der Authentifizierung:
 * 
 * - User gibt username / passwort ein
 * - payload mit `{"username":"...", "password":"..."}` wird an `Auth->postIndexAction()` übertragen
 * - Credentials werden überprüft, JWT wird generiert
 * - Das JWT enthält die `ses_id`, die als Hash in `fe_sessions.ses_id` liegt
 * - JWT im JSON an Frontend gesendet
 * 
 * Requests nach Authentifizierung:
 * 
 * - User schickt Request an Api-Endpoint, der Authentifizierung erfordert
 * - Im `Authorization`-Header wird der Token als `Bearer {token}` übergeben
 * - Diese Middleware wird VOR der Standard Typo3 `FrontendUserAuthenticator` aufgerufen
 * - JWT wird überprüft. Falls valide, wird `fe_typo_user`-Cookie auf `ses_id` gesetzt
 * - Damit "denkt" die Webseite, dass ein `fe_typo_user`-Cookie vom Browser übergeben wurde
 * - Der Typo3 Core `FrontendUserAuthenticator` übernimmt danach den Login anhand des Session-Cookies
 * 
 */
class Jwt extends AbstractAuthenticator {

	/**
	 * Called by Authenticator Middleware.
	 * 
	 * Checks, if a JWT-Token was passed as Authorization Bearer in 
	 * the header of the request.
	 * 
	 * See AbstractAuthenticator for more details
	 * 
	 * @return mixed
	 */
	public function process( $request = null ) {

// REMOVE THIS LINE!!
//$this->simulateAuthorizationBearer();

		$token = TokenService::getFromRequest();
		if ($feUserSessionId = $token['ses_id'] ?? false) {
			return ['feUserSessionId' => $feUserSessionId];
		}

		return false;
	}

	/**
	 * Creates a (fake) Authorizaten Bearer Header so the TokenService
	 * can pick up `$_SERVER['Authorization']` and return a token with
	 * the frontend-user `ses_id`.
	 * 
	 * @return void
	 */
	public function simulateAuthorizationBearer() {

		$session = $this->simulateUserSession();

		$jwt = TokenService::create([
			'uid' 		=> 1, 
			'ses_id' 	=> $session->getIdentifier(),
			'tstamp' 	=> time(),
			'ip'		=> $_SERVER['REMOTE_ADDR']
		]);

		$_SERVER['Authorization'] = 'Bearer ' . $jwt;
	}


}