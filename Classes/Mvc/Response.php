<?php

namespace Nng\Nnrestapi\Mvc;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Response
 * 
 * ```
 * use \Nng\Nnrestapi\Mvc\Response;
 * 
 * $response = new Response();
 * $response->setStatus( Response::OK )->setBody( $data )->send();
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


	public function __construct(
		ResponseFactoryInterface $responseFactory
	) {
		$this->responseFactory = $responseFactory;
		return $this;
	}

	/**
	 * 
	 * @throws PropagateResponseException
	 */
	public function send( $body = [] ) {
		$body = $body ?: $this->getBody();
		$status = $this->getStatus();
		$message = $this->getMessage();
		
		$json = \nn\t3::Convert($body)->toJson();

		$response = $this->responseFactory->createResponse((int)$status, $message);
		$response->getBody()->write($json);
		throw new PropagateResponseException($response, 1476045871);
	}

	/**
	 * Einen Fehler ausgeben
	 * 
	 * @return void
	 */
	public function error( $statusCode, $message = '' ) {
		$this->setStatus($statusCode)->setMessage($message)->send();
	}

	/**
	 * 200 OK
	 * 
	 * @return void 
	 */
	public function success( $body = [], $message = 'OK' ) {
		$this->setStatus(200)->setMessage($message)->send( $body );
	}
	
	/**
	 * 204 No Content
	 * 
	 * @return void 
	 */
	public function noContent( $message = 'No Content' ) {
		$this->setStatus(204)->setMessage( $message )->send();
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