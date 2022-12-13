<?php

namespace Nng\Nnrestapi\Middleware;

use Nng\Nnrestapi\Mvc\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PageResolver MiddleWare.
 * 
 * Takes care of analysing the request and checking, if an Endpoint was defined for the request.
 * Creates an Instance of the `ApiController` which will then handle the actual method call in
 * the Endpoint.
 * 
 * Request handling in MiddleWare / TYPO3 docs:
 * https://bit.ly/3GBcveH
 * 
 */
class PageResolver implements MiddlewareInterface {
		
	/** 
	 * @var \Nng\Nnrestapi\Mvc\Response
	 */
    private $response;

	/**
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

		// URL does not contain the base path to the api (e.g. `/api/...`)? Then abort.
		if ($endpoint === null) {
			return $handler->handle($request);
		}
		
		// `OPTIONS` prerequest? Then abort with "am there, everything ok!"
		if ($method == 'options') {
			$response = \nn\t3::injectClass( \TYPO3\CMS\Core\Http\Response::class );
			\nn\rest::Header()->addControls( $response );
			$response = $response->withStatus( 204, 'No Content' );
			return $response;
		}

		// Should go to API, but URL could be mapped to controller? Output 404
		if (!($endpoint['class'] ?? false)) {

			$args = $endpoint['args'];
			$endpointsForController = \nn\rest::Endpoint()->findEndpointsForController( $args['controller'] );
			$firstEndpoint = array_shift($endpointsForController);
			$className = $firstEndpoint['class'] ?? ucfirst($args['controller']);

			$classMethodInfo = $className . '::' . $method . ucfirst($args['action']) . 'Action()';

			$response = \nn\t3::injectClass(\TYPO3\CMS\Core\Http\Response::class );
			\nn\rest::Header()->addControls( $response );
			$response = $response->withStatus( 404, 'Not found' );
			$response->getBody()->write(json_encode([
				'status'	=> 404, 
				'error'		=> 'RestApi-endpoint not found. Based on your request the endpoint would be `' . $classMethodInfo . '`',
				'code'		=> 404,
			]));
			return $response;
		}

		$settings = \nn\t3::Settings()->get('tx_nnrestapi');

		// Compensate problems with JS date-pickers
		if ($timeZone = $settings['timeZone'] ?? false) {
			date_default_timezone_set( $timeZone );
		}

		$this->response = \nn\t3::injectClass( Response::class );
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