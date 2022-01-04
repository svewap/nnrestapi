<?php

namespace Nng\Nnrestapi\Authenticator;

/**
 * # JWT Authenticator
 * 
 * Authenticates a frontend-user by using a JSON Web Token instead of the `fe_typo_user` cookie.
 * This allows cross-domain authentication without worrying about CORS-problems or cookie-domains.
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
 * - xxxxx The `Authenticator`-MiddleWare is called __before__ the FrontendUserAuthenticator of the Core
 * - This Authenticator checks if the token is valid.
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
	public function process() {

		// Fake JWT for test-purposes. To create a token, use the login-form in the backend-module
		//$_SERVER['Authorization'] = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1aWQiOjksInRzdGFtcCI6MTYzNjM5ODk0OSwiaXAiOiIxMDkuMjUwLjY2LjQifQ==.NjM5MjM0M2I1NjVhNzhmM2NlZGJjYWEyZTRhZDMxMWM1NWE1M2M5MzFiYjFiZGJjZjljMjY0ZDVkYTBlZWU1Yg==';

		// Was a JWT-token passed and is it valid? If not, abort.
		$token = \nn\t3::Request()->getJwt();
		if (!$token) return false;

		// feUserUid stored in token? If not, abort.
		$feUserUid = $token['uid'] ?? false;
		if (!$feUserUid) return false;

		// Session exists in table `nnrestapi_sessions`?
		$session = \nn\rest::Session()->get( $token['token'] );
		if (!$session) return false;
		
		// Update tstamp
		\nn\rest::Session()->touch( $token['token'] );

		// get `fe_user` from DB
		if ($user = \nn\t3::Db()->findByUid( 'fe_users', $feUserUid )) {
			return $user;
		}

		return false;
	}

}