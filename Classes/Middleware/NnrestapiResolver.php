<?php

namespace Nng\Nnrestapi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;


class NnrestapiResolver implements MiddlewareInterface {
	
	/**
	 *  Wird aufgerufen
	 * 
	 *	@param ServerRequestInterface $request
	 *	@param RequestHandlerInterface $handler
	 *	@return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		
		$uri = $request->getUri()->getPath();
		$method = $request->getMethod();

		$response = new Response();
		$response->getBody()->write( $this->getRequestedContent( $request ) );
		
		return $response;
	}
	
	/**
	 *
	 */    
	public function getRequestedContent ( $request = null ) {

		\nn\t3::debug( \nn\rest::Endpoint()->getAll() ); die();
	}

}