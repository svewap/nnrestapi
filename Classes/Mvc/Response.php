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


	public function __construct( $responseFactory = null ) {
		
		$responseFactory = \nn\t3::injectClass( ResponseFactoryInterface::class );
		$this->responseFactory = $responseFactory;
		$this->response = $responseFactory ? $responseFactory->createResponse() : \nn\t3::injectClass(\TYPO3\CMS\Core\Http\Response::class );
		
		\nn\rest::Header()->addControls( $this->response )->addContentType( $this->response );
	}

	/**
	 * 
	 */
	public function render( $body = [] ) {

		$body = $body ?: $this->getBody();
		$status = $this->getStatus();
		$message = $this->getMessage();

		// Convert Model to array and remove fields defined in `settings.globalDistillers`
		$arrayData = \nn\t3::Convert($body)->toArray( 5 );
		\Nng\Nnrestapi\Distiller\ModelDistiller::process( $body, $arrayData );
		$json = json_encode( $arrayData );

		$this->response = $this->response->withStatus( $status, $message );
		$this->response->getBody()->write($json);
		return $this->response;		
	}
	
	/**
	 * 
	 */
	public function exit( $body = null ) {
		$this->render( $body );
		throw new PropagateResponseException($this->response, 1476045871);
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
	 * Not found ausgeben
	 * 
	 * @return void
	 */
	public function notFound( $message = 'Not found.' ) {
		return $this->setStatus(404)->setMessage($message)->render([
			'status'	=> 404, 
			'error'		=> $message
		]);
	}

	/**
	 * 200 OK
	 * 
	 * @return void 
	 */
	public function success( $body = [], $message = 'OK' ) {
		return $this->setStatus(200)->setMessage($message)->render( $body );
	}
	
	/**
	 * 204 No Content
	 * 
	 * @return \TYPO3\CMS\Core\Http\Response 
	 */
	public function noContent( $message = 'No Content' ) {
		return $this->setStatus(204)->setMessage( $message )->render();
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

	/**
	 * @return  \TYPO3\CMS\Core\Http\Response
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * @param   \TYPO3\CMS\Core\Http\Response $response  
	 * @return  self
	 */
	public function setResponse( &$response ) {
		$this->response = $response;
		return $this;
	}
}