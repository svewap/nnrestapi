<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Nnrestapi
 * 
 */
class User extends AbstractApi {
	

	/**
	 * GET user	-> get current fe_user
	 * ?type=20200505&controller=user
	 * 
	 * @access fe_users
	 * @return mixed
	 */
	public function getIndexAction()
	{
		$feUser = \nn\t3::FrontendUser()->getCurrentUser();
		return $feUser;
	}
	
}
