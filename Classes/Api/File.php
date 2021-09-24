<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Nnrestapi
 * 
 */
class File extends AbstractApi {
	
	/**
	 * Upload einer Datei
	 * 
	 * @api\example {"images":[{"publicUrl":"UPLOAD:file-0"}, {"publicUrl":"UPLOAD:file-1"}]}
	 * @api\access fe_users
	 * 
	 * @return array
	 */
	public function postUploadAction( $params = [], $payload = null )
	{
		return [
			'files' => $this->request->getUploadedFiles(),
			'data' => $this->request->getBody(),
		];

		$settings = \nn\t3::Settings()->get('nnrestapi');
		$filepath = $settings['upload']['filepath'];

		$file = $_FILES['file'] ?? false;
		if (!$file) return [];

		$filePath = \nn\t3::File()->moveUploadedFile($file['tmp_name'], $filepath . $file['name']);
		$thumbnail = \nn\t3::File()->process( $filePath, ['width'=>400, 'height'=>'225c'] );
		$result = [
			'publicUrl' => \nn\t3::File()->stripPathSite($filePath),
			'thumbnail' => $thumbnail,
		];
		return $result;
	}

	

}
