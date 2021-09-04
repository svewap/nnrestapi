<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Nnrestapi
 * 
 */
class User extends AbstractApi {
	
	/**
	 * @var \Nng\Nnrestapi\Service\FrontendUserService
	 * @TYPO3\CMS\Extbase\Annotation\Inject
	 */
	protected $frontendUserService;
	

	/**
	 * GET user	-> get current fe_user
	 * ?type=20200505&controller=user
	 * 
	 * @return mixed
	 */
	public function indexAction( $params, $method = '' )
	{
		return $this->frontendUserService->getFeUser();
	}
	
	/**
	 * POST user/auth	-> Login user
	 * ?type=20200505&controller=user&action=auth&username=test&password=test
	 * 
	 * @access public
	 * @return mixed
	 */
	public function postAuthAction( $params, $method = '' )
	{
		
		$feUser = \nn\t3::FrontendUserAuthentication()->login( $params['username'], $params['password'] );
		if (!$feUser) {
			$this->errorUnauthorized();
		}
		
		$token = \Nng\Nnrestapi\Service\TokenService::create(['uid'=>$feUser['uid']]);
		\nn\t3::Db()->update('fe_users', ['nnrestapi_jwt'=>$token], $feUser['uid']);

		return [
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
	}

	/**
	 * GET user/logout	-> Logout user
	 * ?type=20200505&controller=user&action=logout
	 * 
	 * @return mixed
	 */
	public function getLogoutAction( $params, $method = '' )
	{
		$feUser = \nn\t3::FrontendUser()->getCurrentUser();
		if (!$feUser) return [];
		
		\nn\t3::Db()->update('fe_users', ['nnrestapi_jwt'=>''], $feUser['uid']);
		\nn\t3::FrontendUser()->logout();
	}

}
