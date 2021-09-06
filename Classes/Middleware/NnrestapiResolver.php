<?php

namespace Nng\Nnrestapi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;


class NnrestapiResolver implements MiddlewareInterface {
	
	/**
	 *  Wird aufgerufen, wenn in URL &e=nnrestapi übergeben wurde
	 * 
	 *	@param ServerRequestInterface $request
	 *	@param RequestHandlerInterface $handler
	 *	@return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

		return $handler->handle($request);
		
		$uri = $request->getUri()->getPath();	// `/api/controller/action/1/2/3`
		$method = $request->getMethod();		// `GET` / `POST` / `PUT` ...

		\nn\t3::FrontendUser()->login('99grad');
		
		\nn\t3::debug($request); die();

		return $handler->handle($request);

		$e = $request->getParsedBody()['e'] ?? $request->getQueryParams()['e'] ?? null;

		if ($e !== 'nnrestapi') return $handler->handle($request);

		ob_clean();

// ------------------------------------------------
// ENTFERNEN!!
\nn\t3::FrontendUser()->login('99grad');
// ------------------------------------------------

		//\nn\t3::Tsfe()->init();

		$response = new Response();
		$response->getBody()->write( $this->getRequestedContent( $request ) );
		return $response;
	}
	
	
	/**
	 *
	 */    
	public function getRequestedContent ( $request = null ) {


	}
}