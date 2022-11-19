<?php

namespace Nng\Nnrestapi\Mvc;

use \Nng\Nnrestapi\Error\ApiError;

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
     * The standardized and other important HTTP Status Codes
     * @var array
     */
    public $availableStatusCodes = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    ];

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
	 * Normalize an \Error to [$message, $code]
	 * 
	 * @param string|int|ApiError $statusCode
	 * @param string|Error $message
	 * @param string $code
	 * @return array
	 */
	public function normalizeResponseMessage( $statusCode = null, $message = null, $code = null ) 
	{
		if (is_a($statusCode, ApiError::class)) {
			$code = $code ?: $statusCode->getCustomErrorCode();
			$message = $message ?: $statusCode->getMessage();
			$statusCode = $statusCode->getCode();
		}
		if (is_a($message, \Error::class)) {
			$code = $code ?: $message->getCode();
			$message = $message->getMessage();
		}
		if (!isset($this->availableStatusCodes[$statusCode])) {
			$statusCode = 400;
		}
		return [$statusCode, $message, $code];
	}

	/**
	 * Output an error. Actually just a wrapper for setting the status,
	 * message and rendering the Response – could be used for any type
	 * of Response - but makes the intention clearer when used in an 
	 * Endpoint.
	 * 
	 * @param int $statusCode
	 * @param string $message
	 * @param string $code
	 * @return \TYPO3\CMS\Core\Http\Response
	 */
	public function error( $statusCode = 404, $message = '', $code = '' ) 
	{
		[$statusCode, $message, $code] = $this->normalizeResponseMessage($statusCode, $message, $code);
		return $this->setStatus($statusCode)->setMessage($message)->render([
			'status'	=>$statusCode, 
			'error'		=>$message,
			'code'		=>$code,
		]);		
	}
	
	/**
	 * Create an `Unauthorized` (403) Response
	 * 
	 * @param string $message
	 * @param string $code
	 * @return \TYPO3\CMS\Core\Http\Response
	 */
	public function unauthorized( $message = '', $code = '' ) 
	{
		[$statusCode, $message, $code] = $this->normalizeResponseMessage(403, $message, $code);
		if (!$message) $message = 'Unauthorized. Please login.';
		return $this->setStatus($statusCode)->setMessage( $message )->render([
			'status'	=> $statusCode, 
			'error'		=> $message,
			'code'		=> $code,
		]);
	}

	/**
	 * Alias to `unauthorized`.
	 * Makes programmers think less.
	 *
	 * @param string $message
	 * @param string $code
	 * @return \TYPO3\CMS\Core\Http\Response
	 */
	public function forbidden( $message = '', $code = '' ) 
	{
		return $this->unauthorized( $message, $code );
	}

	/**
	 * Creates a `not found` (404) Response
	 * 
	 * @param string $message
     * @return \TYPO3\CMS\Core\Http\Response
	 */
	public function notFound( $message = 'Not found.', $code = '' ) 
	{
		[$statusCode, $message, $code] = $this->normalizeResponseMessage(404, $message, $code);
		return $this->setStatus($statusCode)->setMessage($message)->render([
			'status'	=> $statusCode, 
			'error'		=> $message,
			'code'		=> $code,
		]);
	}
	
	/**
	 * Return an `invalid parameters` (422) Response
	 * 
	 * @param string $message
	 * @param string $code
     * @return \TYPO3\CMS\Core\Http\Response
	 */
	public function invalid( $message = 'Invalid parameters.', $code = '' ) 
	{
		[$statusCode, $message, $code] = $this->normalizeResponseMessage(422, $message, $code);
		return $this->setStatus($statusCode)->setMessage($message)->render([
			'status'	=> $statusCode,
			'error'		=> $message,
			'code'		=> $code,
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