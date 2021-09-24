<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
	 * @api\example {"username":"david", "password":"mypassword"}
	 * @api\access public
	 * @api\distiller Nng\Nnrestapi\Distiller\FeUserDistiller
	 * 
	 * @return mixed
	 */
	public function postIndexAction()
	{
		$params = $this->request->getBody();
		$feUser = \nn\t3::FrontendUserAuthentication()->login( $params['username'], $params['password'] );

		if (!$feUser) {
			$this->response->unauthorized('Invalid credentials.');
		}

		$token = \Nng\Nnrestapi\Service\TokenService::create(['uid'=>$feUser['uid']]);
		
		\nn\t3::Db()->update('fe_users', ['nnrestapi_jwt'=>$token], $feUser['uid']);
		$feUser['nnrestapi_jwt'] = $token;

		return $feUser;
	}


	/**
	 * ## Logout the current FrontendUser.
	 * 
	 * Will unset the user-session and cookie and delete the JWT token for the user in the database.
	 * 
	 * @api\access public
	 * 
	 * @return mixed
	 */
	public function getLogoutAction()
	{
		$feUser = \nn\t3::FrontendUser()->getCurrentUser();
		if (!$feUser) return [];
		
		\nn\t3::Db()->update('fe_users', ['nnrestapi_jwt'=>''], $feUser['uid']);
		\nn\t3::FrontendUser()->logout();
	}

}
