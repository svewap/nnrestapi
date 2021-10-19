<?php

namespace Nng\Nnrestapi\Authenticator;

/**
 * # JWT Authenticator
 * 
 * Authenticates a frontend-user by using a JSON Web Token instead of the `fe_typo_user` cookie.
 * This allows cross-domain authentication without worrying about CORS-problems or cookie-domains.
 * 
 * The trick behind this authentication is rather simple: Instead of relying on the browser to send
 * the default `fe_typo_user`-Cookie and letting TYPO3 parse the `$_COOKIE` in the 
 * `typo3/cms-frontend/authentication` MiddleWare, this script parses the JWT-Token __before__ the 
 * Frontend-Authentcation kicks in. It validates the JWT and then sets the cookie.
 * 
 * Step-by-step explaination:
 * 
 * During authentication:
 * 
 * - User enters his username / password
 * - `Auth->postIndexAction()` is called and payload `{"username":"...", "password":"..."}` passed
 * - `Auth->postIndexAction()` checks credentials and (if valid) generates the JWT.
 * - `\nn\rest::Session()->create()` is called. It generates an entry in `nnrestapi_sessions`.
 * 	 The 'real' TYPO3 sessionId (usually used for the fe-user-cookie) is encrypted and stored
 *   in the column `nnrestapi_sessions.data`
 * - The JWT is sent to the frontend
 * 
 * Requests with JWT:
 * 
 * - User sends a request to an api-endpoint that requires authentication
 * - The `Authorization`-header is sent along with the request. The JWT is passed as `Bearer {jwtToken}`
 * - The `Authenticator`-MiddleWare is called __before__ the FrontendUserAuthenticator of the Core
 * - This Authenticator checks if the token is valid.
 * - If the token is valid, the session-data is loaded from `nnrestapi_session`
 * - The 'real' Typo3-Session-ID is retrieved from `nnrestapi_session.data.sid` 
 * - If the Typo3 session has expired in `fe_sessions`, a new session is automatically generated
 * - The `$_COOKIE['fe_typo_user']` and the `Request.cookieParams.fe_typo_user`-Cookies are set.
 * - After this, the Session-Cookie is ready to be read and processed by the Typo3 Core 
 *   `FrontendUserAuthenticator`. It takes care of the actual login.
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

		// the session-identifier can be any unique string. In this case we are simply using the raw JWT string.
		$sessionIdentifier = \nn\t3::Request()->getBearerToken();

		// Was a JWT-token passed and is it valid? If not, abort.
		$token = \nn\t3::Request()->getJwt();		
		if (!$token) return false;

		// feUserUid stored in token? If not, abort.
		$feUserUid = $token['uid'] ?? false;
		if (!$feUserUid) return false;

		// (Re)start current session or create a new one
		$sessionId = \nn\rest::Session()->start( $sessionIdentifier, $feUserUid, $request );
		
		// something went wrong. Destroy session.
		if (!$sessionId) return false;

		return true;
	}

}