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
		
		// Initialize the Settings singleton. Must be done after `typo3/cms-frontend/site` MiddleWare 
		// and before `\nn\rest::Settings()` is used anywhere
		\nn\rest::Settings()->setRequest( $request );

		// call `process()` on all registered Authenticators
		$authenticators =  \nn\rest::Auth()->getAll();

		// delete expired sessions in table `nnrestapi_sessions`
		\nn\rest::Session()->removeExpiredTokens();

		foreach ($authenticators as $authenticator) {
			if ($classInstance = \nn\t3::injectClass( $authenticator['className'] ?? false )) {
				if ($classInstance->process( $request )) {
					break;
				}
			} else {
				\nn\t3::Exception( "nnrestapi: Registered authenticator {$authenticator['className']} not found." );
			}
		}

		return $handler->handle($request);

	}

}