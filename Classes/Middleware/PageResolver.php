<?php

namespace Nng\Nnrestapi\Middleware;

use Nng\Nnrestapi\Mvc\Response;
use TYPO3\CMS\Core\Context\Context;

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
class PageResolver implements MiddlewareInterface {
	
	/** 
	 * @var ResponseFactoryInterface 
	 */
    private $responseFactory;
	
	/** 
	 * @var \Nng\Nnrestapi\Mvc\Response
	 */
    private $response;

	/**
	 * @var Context
	 */
	protected $context;

	/**
	 * 
	 * @return void
	 */
    public function __construct(Context $context) {
        $this->response = \nn\t3::injectClass( Response::class );
        $this->context = $context;
    }

	/**
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

		$apiRequest = new \Nng\Nnrestapi\Mvc\Request( $request );
		$apiRequest->setFeUser( \nn\t3::FrontendUser()->get() );
		$apiRequest->setEndpoint( $endpoint );
		$apiRequest->setArguments( $endpoint['route']['arguments'] ?? [] );
		$apiRequest->setSettings( \nn\t3::Settings()->get('tx_nnrestapi') );

		$controller = \nn\t3::injectClass( \Nng\Nnrestapi\Controller\ApiController::class );
		$controller->setRequest( $apiRequest );
		$controller->setResponse( $this->response );
		$response = $controller->indexAction();

		return $response;

	}

}