<?php
namespace Nng\Nnrestapi\Api;

use Nng\Nnrestapi\Annotations as Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Nnrestapi
 * 
 */
class Index extends AbstractApi {
	
	/**
	 * Simple test.
	 * 
	 * @Api\Access("*")
	 * 
	 * @return array
	 */
	public function getIndexAction()
	{
		$result = ['message'=>'Successfully called /api. This is mapped to the public endpoint Index->getIndexAction(). Use /api/{controller}/{action} syntax to connect to an endpoint.'];
		return $result;
	}
	
	/**
	 * Simple test.
	 * 
	 * @Api\Access("*")
	 * 
	 * @return array
	 */
	public function postIndexAction()
	{
		$files = $this->request->getUploadedFiles();
		$body = $this->request->getBody();
		return ['POST'=>['body'=>$body, 'files'=>$files]];
	}
	
	/**
	 * Simple test.
	 * 
	 * @Api\Access("*")
	 * 
	 * @return array
	 */
	public function putIndexAction()
	{
		$result = $this->request->getBody();
		return ['PUT'=>$result];
	}
	
	/**
	 * Simple test.
	 * 
	 * @Api\Access("*")
	 * 
	 * @return array
	 */
	public function deleteIndexAction()
	{
		$result = $this->request->getBody();
		return ['DELETE'=>$result];
	}

}
