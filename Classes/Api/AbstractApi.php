<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Nng\Nnrestapi\Mvc\Request;

/**
 * Nnrestapi
 * 
 */
class AbstractApi {

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @return  Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * @param   Request  $request  
	 * @return  self
	 */
	public function setRequest($request) {
		$this->request = $request;
		return $this;
	}


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
