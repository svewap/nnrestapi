<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Nnrestapi
 * 
 */
class AbstractApi {

	/**
	 * 	Einen Fehler ausgeben
	 * 
	 */
	public function error( $statusCode, $message = '' ) {
		$data = [
			'status' 	=> $statusCode,
			'message'	=> $message,
		];
		header("HTTP/1.0 {$statusCode} {$message}");
		echo json_encode($data);
		die();
	}
	
	/**
	 * 	204 OK
	 * 
	 */
	public function success( $message = '' ) {
		$this->error(200, $message ?: 'OK');
	}
	
	/**
	 * 	400 BAD REQUEST
	 * 
	 */
	public function bad( $message = '' ) {
		$this->error(400, $message ?: 'Bad request.');
	}
	
	/**
	 * 	404 Nicht gefunden
	 * 
	 */
	public function errorNotFound( $message = '' ) {
		$this->error(404, $message ?: 'Not found.');
	}

	/**
	 * 	403 Unauthorized
	 * 
	 */
	public function errorUnauthorized( $message = '' ) {
		$this->error(403, $message ?: 'Unauthorized.');
	}

}
