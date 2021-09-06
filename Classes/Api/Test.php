<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Nnrestapi
 * 
 */
class Test extends AbstractApi {
	
	/**
	 * Einfacher Test
	 * 
	 * GET test/
	 * ?type=20200505&controller=file&action=upload
	 * 
	 * @access public
	 * @return array
	 */
	public function getIndexAction( $params = [], $payload = null )
	{
		$result = ['OK'=>123];
		return $result;
	}

	

}
