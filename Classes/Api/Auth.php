<?php
namespace Nng\Nnrestapi\Api;

use Nng\Nnrestapi\Annotations as Api;
use Nng\Nnrestapi\Distiller\FeUserDistiller;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Nnrestapi
 *
 */
class Auth extends AbstractApi {
	
	/**
	 * ## Authenticate a frontend-user (`fe_user`).
	 * 
	 * Login of a frontend user. Returns a JWT token and sets the `fe_typo3_user`-cookie.
	 * The RESTApi will try to login the frontend-user using the cookie. If this fails, e.g.
	 * because of cross-domain-cookie restrictions, it will use the JWT. 
	 *
	 * @Api\Example("{'username':'david', 'password':'mypassword'}")
	 * @Api\Access("*")
	 * @Api\Distiller( FeUserDistiller::class )
	 * 
	 * @return mixed
	 */
	public function postIndexAction()
	{
		$params = $this->request->getBody();
		$feUser = \nn\t3::FrontendUserAuthentication()->login( $params['username'], $params['password'] );

		if (!$feUser) {
			return $this->response->unauthorized('Invalid credentials.');
		}

		$token = \Nng\Nnrestapi\Service\TokenService::create([
			'uid' 		=> $feUser['uid'], 
			'ses_id' 	=> \nn\t3::FrontendUser()->getSessionId(),
			'tstamp' 	=> time(),
			'ip'		=> $_SERVER['REMOTE_ADDR']
		]);
		
		$feUser['token'] = $token;
		return $feUser;
	}


	/**
	 * ## Logout the current FrontendUser.
	 * 
	 * Will unset the user-session and cookie.
	 * 
	 * @Api\Access("*")
	 * 
	 * @return mixed
	 */
	public function getLogoutAction()
	{
		$feUser = \nn\t3::FrontendUser()->getCurrentUser();
		if (!$feUser) return ['message'=>'User already logged out.'];
		
		\nn\t3::FrontendUser()->logout();
		return ['message'=>'User successfully logged out.'];
	}

}
