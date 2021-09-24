<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Nng\Nnrestapi\Domain\Model\ApiTest;

/**
 * Nnrestapi
 * 
 * Beispiele für Routing per @api\route:
 * ```
 * @api\route /test/demo
 * @api\route /test/demo/{uid} 
 * @api\route /test/demo/{uid?}
 * @api\route /test/demo/{uid}/{test}
 * @api\route /test/demo/{uid?}/{test?}
 * @api\route GET /test/demo/something
 * @api\route GET|POST|PUT /test/demo/something
 * @api\route GET auth/log_me_out/{uid}/{something}
 * ```
 */
class Test extends AbstractApi {

	/**
	 * ## Simple test
	 * 
	 * Shows the parsed JSON from the body-field and the file-uploads.
	 * Will not copy any files to server. For testing a real file-upload and the
	 * conversion to a FileReference: Use `api/test/upload/`.
	 * 
	 * @api\access be_users
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
	 * You can define the target path/folder by using the `@api\upload myconf` __annoation__ 
	 * in the comment of your endpoint's method. The configuration is set in the
	 * TypoScript setup: 
	 * ```
	 * plugin.tx_nnrestapi.settings.fileUploads {
	 * 	// Use this key for "UPLOAD:myconf:/..." and "@api\upload myconf"
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
	 * @api\upload default
	 * @api\example {"title":"My Test Model", "files":["UPLOAD:/file-0", "UPLOAD:/file-1"]}
	 * @api\access be_users
	 * 
	 * @return array
	 */
	public function postUploadAction( ApiTest $apiTest = null )
	{
		return $apiTest;

		$files = [];
		foreach ($this->request->getUploadedFiles() as $k=>$file) {
			$files[$k] = $file->getClientFilename();
		}

		$result = [
			'body' 	=> $this->request->getBody() ?: 'Put a JSON in the body-field to test!',
			'files'	=> $files ?: 'Select a file to test!'
		];
		return $result;
	}


}
