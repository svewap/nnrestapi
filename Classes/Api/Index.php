<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Nnrestapi
 * 
 */
class Index extends AbstractApi {
	
	/**
	 * Einfacher Test
	 * 
	 * GET api/
	 * ?type=20200505
	 * 
	 * @access public
	 * @return array
	 */
	public function getIndexAction( $params = [], $payload = null )
	{
		$result = ['message'=>'Successfully called /api. This is mapped to endpoint Index->getIndexAction(). Use /api/{controller}/{action} syntax to connect to an endpoint.'];
		return $result;
	}

	

}
