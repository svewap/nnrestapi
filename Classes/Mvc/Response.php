<?php

namespace Nng\Nnrestapi\Mvc;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Response
 * 
 * ```
 * use \Nng\Nnrestapi\Mvc\Response;
 * 
 * $response = new Response();
 * $response->setStatus( Response::OK )->setBody( $data );
 * return $response->render();
 * ```
 */
class Response {

	/**
	 * @var int
	 */
	protected $status = 200;
	
	/**
	 * @var string
	 */
	protected $message = '';

	/**
	 * @var array
	 */
	protected $body = [];

	/**
	 * @var ResponseFactoryInterface
	 */
	protected $responseFactory;

	/**
	 * @var \TYPO3\CMS\Core\Http\Response
	 */
	protected $response;


	public function __construct( $response = null ) {
		$this->response = $response;
		return $this;
	}

	/**
	 * 
	 * @throws PropagateResponseException
	 */
	public function render( $body = [] ) {

		$body = $body ?: $this->getBody();
		$status = $this->getStatus();
		$message = $this->getMessage();
		
		$json = \nn\t3::Convert($body)->toJson( 5 );
		$this->response->setStatus( $status, $message );

		/*		
		$response = $this->responseFactory->createResponse((int)$status, $message);
		$response->getBody()->write($json);
		throw new PropagateResponseException($response, 1476045871);
		*/
		
		return $json;
	}
	
	/**
	 * Einen Fehler ausgeben
	 * 
	 * @return void
	 */
	public function error( $statusCode, $message = '' ) {
		return $this->setStatus($statusCode)->setMessage($message);
		return [
			'status'	=>$statusCode, 
			'error'		=>$message
		];
	}
	
	/**
	 * Unauthorized Fehler ausgeben
	 * 
	 * @return void
	 */
	public function unauthorized( $message = '' ) {
		if (!$message) $message = 'Unauthorized. Please login.';
		$this->setStatus(403)->setMessage( $message );
		return [
			'status'	=> 403, 
			'error'		=> $message
		];
	}

	/**
	 * 200 OK
	 * 
	 * @return void 
	 */
	public function success( $body = [], $message = 'OK' ) {
		$this->setStatus(200)->setMessage($message);
		return $body;
	}
	
	/**
	 * 204 No Content
	 * 
	 * @return void 
	 */
	public function noContent( $message = 'No Content' ) {
		return $this->setStatus(204)->setMessage( $message );
	}

	/**
	 * @return  array
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @param   array  $body  
	 * @return  self
	 */
	public function setBody($body) {
		$this->body = $body;
		return $this;
	}

	/**
	 * @return  int
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param   int  $status  
	 * @return  self
	 */
	public function setStatus($status) {
		$this->status = $status;
		return $this;
	}

	/**
	 * @return  string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @param   string  $message  
	 * @return  self
	 */
	public function setMessage($message) {
		$this->message = $message;
		return $this;
	}
}