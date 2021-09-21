<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Nnrestapi
 * 
 */
class Auth extends AbstractApi {
	
	/**
	 * Authenticate a frontend-user (`fe_user`).
	 *
	 * @api\example {"username":"david", "password":"99grad"}
	 * @api\access public
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

		$result = [
			'token'	=> $token,
			'user'	=> [
				'uid' => $feUser['uid'],
				'email' => $feUser['email'],
				'firstname' => $feUser['first_name'],
				'lastname' => $feUser['last_name'],
				'username' => $feUser['username'],
				'usergroups' => \nn\t3::Arrays($feUser['usergroup'])->intExplode(),
			] 
		];
		return $result;
	}

	/**
	 * Logout a user.
	 * 
	 * @api\route GET auth/log_me_out/{uid}/{something}
	 * 
	 * @return mixed
	 */
	public function greatLogoutAction()
	{
		$feUser = \nn\t3::FrontendUser()->getCurrentUser();
		if (!$feUser) return [];
		
		\nn\t3::Db()->update('fe_users', ['nnrestapi_jwt'=>''], $feUser['uid']);
		\nn\t3::FrontendUser()->logout();
	}

}
