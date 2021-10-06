<?php

namespace Nng\Nnrestapi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * ## Authenticator
 * 
 * Runs through all Authenticators registered in the `ext_localconf.php` using 
 * `\nn\rest::Auth()->register();`. Calls the `process()` method and sets the user cookie.
 * 
 * See `\Nng\Nnrestapi\Authenticator\Jwt` for more infos.
 * 
 */
class Authenticator implements MiddlewareInterface {
	
	/**
	 * 
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 * @return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		
		$authenticators =  \nn\rest::Auth()->getAll();

		foreach ($authenticators as $authenticator) {
			if ($classInstance = \nn\t3::injectClass( $authenticator['className'] ?? false )) {
				if ($result = $classInstance->process( $request )) {
					if (is_array($result) && $sessionId = $result['feUserSessionId'] ?? false) {
						\nn\t3::FrontendUser()->setCookie( $sessionId );
					}
					break;
				}
			} else {
				\nn\t3::Exception( "nnrestapi: Registered authenticator {$authenticator['className']} not found." );
			}
		}

		return $handler->handle($request);

	}

}