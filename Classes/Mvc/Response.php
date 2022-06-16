<?php

namespace Nng\Nnrestapi\Mvc;

use TYPO3\CMS\Core\Http\PropagateResponseException;
use Psr\Http\Message\ResponseFactoryInterface;

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
class Response 
{
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

	/**
	 * @var array
	 */
	protected $settings;
	
	/**
	 * @var array
	 */
	protected $endpoint;


	/**
	 * Constructor
	 * 
	 * @param ResponseFactoryInterface $responseFactory
	 * @return void
	 */
	public function __construct( $responseFactory = null ) 
	{	
		$responseFactory = \nn\t3::injectClass( ResponseFactoryInterface::class );
		$this->responseFactory = $responseFactory;
		$this->response = $responseFactory ? $responseFactory->createResponse() : \nn\t3::injectClass(\TYPO3\CMS\Core\Http\Response::class );
		
		\nn\rest::Header()->addControls( $this->response )->addContentType( $this->response );
	}

    /**
     * @param $body
     *
     * @return \TYPO3\CMS\Core\Http\Response
     */
	public function render( $body = [] ) 
	{
		$body = $body ?: $this->getBody();
		$status = $this->getStatus();
		$message = $this->getMessage();

		// Convert Model to array and remove fields defined in `settings.globalDistillers`
		$depth = $this->endpoint['json']['depth'] ?? 10;
		$arrayData = \nn\t3::Convert($body)->toArray( $depth );

		\Nng\Nnrestapi\Distiller\ModelDistiller::process( $body, $arrayData );
		$json = json_encode( $arrayData );

		$this->response = $this->response->withStatus( $status, $message );
		$this->response->getBody()->write($json);
		return $this->response;		
	}
	
	/**
	 * Exit (die) and stop TYPO3 from doing any other processing
	 * 
	 * @throws PropagateResponseException
	 * @return void
	 */
	public function exit( $body = null ) 
	{
		$this->render( $body );
		throw new PropagateResponseException($this->response, 1476045871);
	}

	/**
	 * Output an error. Actually just a wrapper for setting the status,
	 * message and rendering the Response – could be used for any type
	 * of Response - but makes the intention clearer when used in an 
	 * Endpoint.
	 * 
	 * @param int $statusCode
	 * @param string $message
	 * @return \TYPO3\CMS\Core\Http\Response
	 */
	public function error( $statusCode = 404, $message = '' ) 
	{
		return $this->setStatus($statusCode)->setMessage($message)->render([
			'status'	=>$statusCode, 
			'error'		=>$message
		]);		
	}
	
	/**
	 * Create an `Unauthorized` (403) Response
	 * 
	 * @param string $message
	 * @return \TYPO3\CMS\Core\Http\Response
	 */
	public function unauthorized( $message = '' ) 
	{
		if (!$message) $message = 'Unauthorized. Please login.';
		return $this->setStatus(403)->setMessage( $message )->render([
			'status'	=> 403, 
			'error'		=> $message
		]);
	}

	/**
	 * Alias to `unauthorized`.
	 * Makes programmers think less.
	 *
	 * @param string $message
	 * @return \TYPO3\CMS\Core\Http\Response
	 */
	public function forbidden( $message = '' ) 
	{
		return $this->unauthorized( $message );
	}

	/**
	 * Creates a `not found` (404) Response
	 * 
	 * @param string $message
     * @return \TYPO3\CMS\Core\Http\Response
	 */
	public function notFound( $message = 'Not found.' ) 
	{
		return $this->setStatus(404)->setMessage($message)->render([
			'status'	=> 404, 
			'error'		=> $message
		]);
	}
	
	/**
	 * Return an `invalid parameters` (422) Response
	 * 
	 * @param string $message
     * @return \TYPO3\CMS\Core\Http\Response
	 */
	public function invalid( $message = 'Invalid parameters.' ) 
	{
		return $this->setStatus(422)->setMessage($message)->render([
			'status'	=> 422, 
			'error'		=> $message
		]);
	}

	/**
	 * Return an `found, OK` (200) Response
	 * 
	 * @param array $body
	 * @param string $message
     * @return \TYPO3\CMS\Core\Http\Response
	 */
	public function success( $body = [], $message = 'OK' ) 
	{
		return $this->setStatus(200)->setMessage($message)->render( $body );
	}
	
	/**
	 * Return an `No Content` (204) Response
	 * 
	 * @param string $message
	 * @return \TYPO3\CMS\Core\Http\Response
	 */
	public function noContent( $message = 'No Content' ) 
	{
		return $this->setStatus(204)->setMessage( $message )->render();
	}

	/**
	 * Return the body of the Response
	 * 
	 * @return  array
	 */
	public function getBody() 
	{
		return $this->body;
	}

	/**
	 * Set the body of the Response
	 * 
	 * @param   array  $body  
	 * @return  self
	 */
	public function setBody($body) 
	{
		$this->body = $body;
		return $this;
	}

	/**
	 * Return the status-code of the Response
	 * 
	 * @return  int
	 */
	public function getStatus() 
	{
		return $this->status;
	}

	/**
	 * Sets the status-code for the Response
	 * 
	 * @param   int  $status  
	 * @return  self
	 */
	public function setStatus($status) 
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * Returns the message of the Response
	 * 
	 * @return  string
	 */
	public function getMessage() 
	{
		return $this->message;
	}

	/**
	 * Sets the message of the Response
	 * 
	 * @param   string  $message  
	 * @return  self
	 */
	public function setMessage($message) 
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * Return the currently generated Response-Object itself
	 * 
	 * @return  \TYPO3\CMS\Core\Http\Response
	 */
	public function getResponse() 
	{
		return $this->response;
	}

	/**
	 * Sets the Response
	 * 
	 * @param   \TYPO3\CMS\Core\Http\Response $response  
	 * @return  self
	 */
	public function setResponse( &$response ) 
	{
		$this->response = $response;
		return $this;
	}

	/**
	 * Get the settings
	 * 
	 * @return  array
	 */
	public function getSettings() 
	{
		return $this->settings;
	}

	/**
	 * Sets the settings
	 * 
	 * @param   array  $settings  
	 * @return  self
	 */
	public function setSettings($settings) 
	{
		$this->settings = $settings;
		return $this;
	}

	/**
	 * Gets information about the current endpoint
	 * 
	 * @return  array
	 */
	public function getEndpoint() 
	{
		return $this->endpoint;
	}

	/**
	 * Sets information about the current endpoint
	 * 
	 * @param   array  $endpoint  
	 * @return  self
	 */
	public function setEndpoint($endpoint) 
	{
		$this->endpoint = $endpoint;
		return $this;
	}
}