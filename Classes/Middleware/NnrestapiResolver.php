<?php

namespace Nng\Nnrestapi\Middleware;

use Nng\Nnrestapi\Mvc\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * 
 * https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/RequestHandling/Index.html
 * 
 */
class NnrestapiResolver implements MiddlewareInterface {
	
	/** 
	 * @var ResponseFactoryInterface 
	 */
    private $responseFactory;
	
	/** 
	 * @var \Nng\Nnrestapi\Mvc\Response
	 */
    private $response;

	/**
	 * 
	 * @return void
	 */
    public function __construct() {
        $this->response = \nn\t3::injectClass( Response::class );
    }

	/**
	 *  Wird aufgerufen
	 * 
	 *	@param ServerRequestInterface $request
	 *	@param RequestHandlerInterface $handler
	 *	@return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		
		$method = strtolower($request->getMethod());
		$endpoint = \nn\rest::Endpoint()->findForRequest( $request );

		// URL enthält nicht den Basispfad zur Api (z.B. `/api/...`)? Dann abbrechen.
		if ($endpoint === null) {
			return $handler->handle($request);
		}

		// `OPTIONS` prerequest? Dann Abbruch mit "bin da, alles ok!"
		if ($method == 'options') {
			return $this->response->noContent();
		}

		// Sollte an API gehen, aber URL konnte auf Controller gemapped werden? 404 ausgeben
		if (!($endpoint['class'] ?? false)) {
			$args = $endpoint['args'];
			$classMethodInfo = ucfirst($args['controller']) . '-&gt;' . $method . ucfirst($args['action']) . 'Action()';
			return $this->response->notFound('RestApi-endpoint not found. Based on your request the endpoint would be `' . $classMethodInfo . '`' );
		}

\nn\t3::debug('RESOLVER');

		$apiRequest = new \Nng\Nnrestapi\Mvc\Request( $request );

		$controller = \nn\t3::injectClass( \Nng\Nnrestapi\Controller\ApiController::class );
		$controller->setRequest( $apiRequest );
		$controller->setResponse( $this->response );
		$response = $controller->indexAction();

		return $this->response->success(['ok'=>'nice!']);


die('NOPE');

		// '/api/{controller}/{action}/{uid}/{param1}/{param2}/{param3}'

		//$response = $this->responseFactory->createResponse();
		$apiRequest = new \Nng\Nnrestapi\Mvc\Request( $request );
		$apiResponse = new \Nng\Nnrestapi\Mvc\Response( $response );

		$apiRequest->setEndpoint( $endpoint );

		// $controller = new \Nng\Nnrestapi\Controller\ApiController( $apiRequest, $apiResponse );
		// $response = $controller->indexAction();
		
		//\nn\t3::debug( $uri ); die();
		\nn\t3::debug( $apiRequest ); die();

		// $response = new \TYPO3\CMS\Core\Http\Response();
		// $response->getBody()->write( $this->getRequestedContent( $request ) );
		
		//return $this->getResponse( $request );
	}
	
	
	/**
	 * Prüft den requestType (GET, POST, ...)
	 * Gibt den requestType in lowerCase zurück.
	 * 
	 * Bricht ab, falls requestType unbekannt ist – oder `OPTIONS` verlangt wird.
	 * 
	 * @return string
	 */
	public function checkRequestType( $request, $response ) {

		$httpMethod = $request->getMethod();

		switch ($httpMethod) {
			case 'head':
			case 'get':
			case 'post':
			case 'put':
			case 'patch':
			case 'delete':
				return $httpMethod;
			case 'options':
				return $response->noContent();
			default:
				return $response->success();
		}
	}


	/**
	 *
	 */    
	public function getResponse ( $request = null ) {

		$controller = \nn\t3::injectClass( \Nng\Nnrestapi\Controller\ApiController::class );
		$controller->request = $request;
		$response = $controller->indexAction();

		\nn\t3::debug( $response ); die();
	}

}