<?php

namespace Nng\Nnrestapi\Authenticator;

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
	public function process( &$request = null ) {

		// Was a token passed? Also checks if token is valid and signature is correct
		if ($token = \nn\t3::Request()->getJwt()) {
			
			// the raw, encoded JWT-string
			$rawBearerToken = \nn\t3::Request()->getBearerToken();

			// Delete expired tokens
			\nn\rest::Session()->removeExpiredTokens();

			// Get session-data for token from database
			$session = \nn\rest::Session()->get( $rawBearerToken );

			// Session expired or invalid?
			if (!$session) return false;

			// Return the feUser.uid to the Authenticator-Middleware
			if ($feUserUid = $token['uid'] ?? false) {

				// Does Typo3-session still exist in `fe_sessions`?
				$oldSessionId = $session['data']['sid'] ?? false;
				if (!$oldSessionId) return false;

				// (Re)start current session or create a new one in `fe_sessions`
				$sessionId = \nn\rest::Session()->restart( $feUserUid, $oldSessionId );			

				// something went wrong. Destroy session.
				if (!$sessionId) return false;

				// update tstamp and sessionId in `nnrest_sessions`
				\nn\rest::Session()->update( $rawBearerToken, ['sid'=>$sessionId] );

				// Override `fe_typo_user`-Cookie in `$_COOKIE` and in the current `Request`
				\nn\t3::FrontendUser()->setCookie( $sessionId, $request );

				return true;
			}
		}

		return false;
	}

}