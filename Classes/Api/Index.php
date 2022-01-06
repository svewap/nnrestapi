<?php
namespace Nng\Nnrestapi\Api;

use Nng\Nnrestapi\Annotations as Api;

/**
 * Nnrestapi
 * 
 */
class Index extends AbstractApi 
{
	/**
	 * # Simple GET test
	 * 
	 * If you are logged in as a backend user, you can test this endpoint by sending a GET-request to:
	 * 
	 * ```
	 * https://www.mysite.com/api/
	 * ```
	 * 
	 * @Api\Access("be_users")
	 * 
	 * @return array
	 */
	public function getIndexAction()
	{
		$result = ['message'=>'Successfully called /api. This is mapped to the public endpoint Index->getIndexAction(). Use /api/{controller}/{action} syntax to connect to an endpoint.'];
		return $result;
	}
	
	/**
	 * # Simple POST test
	 * 
	 * If you are logged in as a backend user, you can test this endpoint by sending a POST-request to:
	 * 
	 * It will return the JSON you passed and the list of files you attached.
	 * No files will be copied to the server because no `@Api\Upload()` Annotation was set on the method.
	 * 
	 * ```
	 * https://www.mysite.com/api/
	 * ```
	 * 
	 * @Api\Access("be_users")
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
	 * # Simple PUT test
	 * 
	 * If you are logged in as a backend user, you can test this endpoint by sending a PUT-request to:
	 * 
	 * It will return the JSON you passed and the list of files you attached.
	 * No files will be copied to the server because no `@Api\Upload()` Annotation was set on the method.
	 * 
	 * ```
	 * https://www.mysite.com/api/
	 * ```
	 * 
	 * @Api\Access("be_users")
	 * 
	 * @return array
	 */
	public function putIndexAction()
	{
		$files = $this->request->getUploadedFiles();
		$body = $this->request->getBody();
		return ['PUT'=>['body'=>$body, 'files'=>$files]];
	}

	/*	
	 * # Simple DELETE test
	 * 
	 * If you are logged in as a backend user, you can test this endpoint by sending a PUT-request to:
	 * 
	 * It will return the JSON you passed. Nothing will be deleted - it is just a debug function.
	 * 
	 * ```
	 * https://www.mysite.com/api/
	 * ```
	 * @Api\Access("be_users")
	 * 
	 * @return array
	 */
	public function deleteIndexAction()
	{
		$result = $this->request->getBody();
		return ['DELETE'=>$result];
	}

}
