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

		$_GP = $request->getParsedBody() ?: $request->getQueryParams();
		
		$action = $_GP['action'];
		$uid = (int) $_GP['uid'];
		$key = $_GP['key'];
		
		$mainController = \nn\t3::injectClass( \Nng\Nnrestapi\Controller\MainController::class );

		// ------------------------------------------
		// Aktionen ohne Authentifizierung
				
		if (method_exists($mainController, $action)) {
			return json_encode($mainController->$action());
		}
	
		
		// ------------------------------------------
		// Aktionen mit Validierung
		
		if ($action) {
			die("Validierung fehlgeschlagen.");
		}
	
		if ($action == 'example') {
			$message = $mainController->approveAction( $uid );
		}

		if ($message = \nn\t3::Message()->render()) {
			$message .= '
				<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
				<link rel="stylesheet" type="text/css" href="'.\nn\t3::Environment()->getBaseURL().'typo3conf/ext/Nnrestapi/Resources/Public/Css/eid.css">
			';
			return $message;
		}
		
		return 'MiddleWare action: <b>'.$action.'</b> aufgerufen.';
	}
}