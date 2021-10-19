<?php
namespace Nng\Nnrestapi\Api;

use Nng\Nnrestapi\Domain\Model\ApiTest;
use Nng\Nnrestapi\Annotations as Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Nnrestapi
 * 
 */
class Test extends AbstractApi {

	/**
	 * ## Simple test
	 * 
	 * Shows the parsed JSON from the body-field and the file-uploads.
	 * Will not copy any files to server. For testing a real file-upload and the
	 * conversion to a FileReference: Use `api/test/upload/`.
	 * 
	 * @Api\Access("be_users")
	 * 
	 * @return array
	 */
	public function postIndexAction()
	{
		$files = [];
		foreach ($this->request->getUploadedFiles() as $k=>$file) {
			$files[$k] = $file->getClientFilename();
		}

		$result = [
			'body' 	=> $this->request->getBody() ?: 'Put a JSON in the body-field to test!',
			'files'	=> $files ?: 'Select one or more files to test!'
		];
		return $result;
	}

	
	/**
	 * ## Test with custom routing
	 * 
	 * This endpoint will be accessible via the URL `/test/route`.
	 * The route was defined using the `@Api\Route` annotation. The name of the
	 * method can then have any arbitrary name and must not follow the scheme
	 * `{reqMethod}{actionName}Action`
	 * 
	 * @Api\Route("GET /test/route")
	 * @Api\Access("be_users")
	 * 
	 * @return array
	 */
	public function customRoutingTest()
	{
		return ['message'=>'customRoutingTest() - routing with @Api\Route works! '];
	}

	/**
	 * ## Test with FileReferences
	 * 
	 * Will copy the uploaded file(s) to the `fileadmin` and create a `ApiTestModel`
	 * with a `FileReference` attached to it. You can reference the uploaded files using
	 * the syntax `UPLOAD:/file-x` in the JSON:
	 * ```
	 * // Simple string:
	 * {"file":"UPLOAD:/file-0"}
	 * {"files":["UPLOAD:/file-0", "UPLOAD:/file-1", ...]}
	 * 
	 * // Or using the publicUrl key:
	 * {"file":{"publicUrl":"UPLOAD:/file-0"}}
	 * {"file":[{"publicUrl":"UPLOAD:/file-0"}, {"publicUrl":"UPLOAD:/file-1"}, ...]}
	 * ```
	 * You can define the target path/folder by using the `@Api\Upload("myconf")` __annoation__ 
	 * in the comment of your endpoint's method. The configuration is set in the
	 * TypoScript setup: 
	 * ```
	 * plugin.tx_nnrestapi.settings.fileUploads {
	 * 	// Use this key in @Api\Upload("myconf")
	 * 	myconf {
	 * 		// if nothing else fits, use fileadmin/
	 * 		defaultStoragePath = 1:/
	 * 		
	 * 		// Optional: Use a custom class to return configuration
	 * 		// pathFinderClass = Nng\Nnrestapi\Helper\UploadPathHelper::getUserUidPath
	 * 		
	 * 		// target-path for file, file-0, file-1 etc. in file storage 1:/ (= fileadmin)
	 * 		file = 1:/myfolder/in/fileadmin/
	 * 		
	 * 		// target path for other, other-0, ...
	 * 		other = fileadmin/path/uploads/
	 * 	}
	 * }
	 * 
	 * @Api\Upload("default")
	 * @Api\Example("{'title':'My Test Model', 'files':['UPLOAD:/file-0', 'UPLOAD:/file-1']}")
	 * @Api\Access("be_users")
	 * 
	 * @return array
	 */
	public function postUploadAction( ApiTest $apiTest = null )
	{
		return $apiTest;
	}


	/**
	 * ## Insert TestApi Model
	 * 
	 * Inserts a new TestApi Model in the database.
	 *
	 * @Api\Example("{'title':'My Test Model', 'files':['UPLOAD:/file-0', 'UPLOAD:/file-1']}")
	 * @Api\Access("be_users")
	 * 
	 * @param Nng\Nnrestapi\Domain\Model\ApiTest $apiTest
	 * @return array
	 */
	public function postAddAction( $apiTest = null )
	{
		\nn\t3::Db()->insert( $apiTest );
		return $apiTest;
	}

	/**
	 * ## Simple test with DELETE
	 * 
	 * Endpoint for the RequestType `DELETE`.
	 *
	 * @Api\Route("DELETE /test/{uid}")
	 * @Api\Access("be_users")
	 * 
	 * @param Nng\Nnrestapi\Domain\Model\ApiTest $apiTest
	 * @return array
	 */
	public function deleteIndexAction( $apiTest = null, $uid = null )
	{
		if (!$apiTest) {
			return $this->response->notFound("Entry with uid {$uid} was not found in database.");
		}

		\nn\t3::Db()->delete( $apiTest );

		$result = [
			'message' => 'DELETE Action successfully called.',
			'body' 	=> "Entry with uid [{$uid}] was deleted in database",
		];

		return $result;
	}
	
	/**
	 * ## Simple test with GET
	 * 
	 * Endpoint for the RequestType `GET`.
	 * 
	 * @Api\Route("GET /test/{uid}")
	 * @Api\Access("be_users")
	 * @Api\IncludeHidden
	 * 
	 * @return array
	 */
	public function getIndexAction( ApiTest $apiTest = null, $uid = null )
	{
		if (!$apiTest) {
			return $this->response->notFound("Entry with uid {$uid} was not found in database.");
		}

		return $apiTest;
	}
	
	/**
	 * ## PUT Example
	 * 
	 * Updates an existing ApiModel. You can create a new ApiModel by calling
	 * `POST /api/test/add`
	 *
	 * @Api\Route("PUT /test/{uid}")
	 * @Api\Upload("default")
	 * @Api\Example("{'title':'My Test Model', 'files':['UPLOAD:/file-0', 'UPLOAD:/file-1']}")
	 * @Api\Access("be_users")
	 * @Api\IncludeHidden
	 * 
	 * @return array
	 */
	public function putIndexAction( ApiTest $apiTest = null )
	{
		$uid = $this->request->getArguments()['uid'];
		if (!$apiTest) {
			return $this->response->notFound('Model with uid [' . $uid . '] was not found.');
		}
		\nn\t3::Db()->update( $apiTest );
		return $apiTest;
	}
	
	/**
	 * ## Simple test with PATCH
	 * 
	 * Endpoint for the RequestType `PATCH`.
	 *
	 * @Api\Upload default
	 * @Api\Example("{'title':'My Test Model', 'files':['UPLOAD:/file-0', 'UPLOAD:/file-1']}")
	 * @Api\Access("be_users")
	 * @Api\IncludeHidden
	 * 
	 * @return array
	 */
	public function patchIndexAction( ApiTest $apiTest = null )
	{
		return $apiTest;
	}

}
