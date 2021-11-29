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
	 * 
	 * @return void
	 */
    public function __construct() {
        $this->response = \nn\t3::injectClass( Response::class );
    }

	/**
	 * 
	 *	@param ServerRequestInterface $request
	 *	@param RequestHandlerInterface $handler
	 *	@return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		
		// Initialize the Settings singleton. Must be done after `typo3/cms-frontend/site` MiddleWare 
		// and before `\nn\rest::Settings()` is used anywhere
		\nn\rest::Settings()->setRequest( $request );

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

		$settings = \nn\t3::Settings()->get('tx_nnrestapi');

		$this->response->setEndpoint( $endpoint );
		$this->response->setSettings( $settings );

		$apiRequest = new \Nng\Nnrestapi\Mvc\Request( $request );
		$apiRequest->setFeUser( \nn\t3::FrontendUser()->get() );
		$apiRequest->setEndpoint( $endpoint );
		$apiRequest->setArguments( $endpoint['route']['arguments'] ?? [] );
		$apiRequest->setSettings( $settings );

		$controller = \nn\t3::injectClass( $settings['apiController'] );
		$controller->setRequest( $apiRequest );
		$controller->setResponse( $this->response );
		$controller->setSettings( $settings );

		try {
			$response = $controller->indexAction();
		} catch( \Exception $e ) {
			\nn\rest::Header()->exception( $e->getMessage(), 500 );
			\nn\t3::Exception( $e->getMessage() );
		} catch( \Error $e ) {
			\nn\rest::Header()->exception( $e->getMessage(), 500 );
			\nn\t3::Error( $e->getMessage() );
		}

		return $response;

	}

}