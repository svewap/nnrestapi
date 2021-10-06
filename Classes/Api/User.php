<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Nnrestapi
 * 
 */
class User extends AbstractApi {
	

	/**
	 * ## Get the current FrontendUser.
	 * 
	 * Returns the currently logged in `fe_user`.
	 * If no user is logged in, an empty array will be returned.
	 * 
	 * @api\access public
	 * @api\distiller Nng\Nnrestapi\Distiller\FeUserDistiller
	 * 
	 * @return array
	 */
	public function getIndexAction()
	{
		$feUser = \nn\t3::FrontendUser()->get();
		return $feUser;
	}
	
}
