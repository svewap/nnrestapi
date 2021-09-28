<?php
declare(strict_types = 1);

namespace Nng\Nnrestapi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;

/**
 * ## RequestParser
 * 
 * Erlaubt das Verarbeiten von RequestMethods, die standardmäßig nicht von PHP 
 * unterstützt werden, z.B. `PUT` und `PATCH`.
 * 
 * In `ext_localconf.php` und `Configuration/Middlewares.php` registrierter 
 * Handler für HTTP-Request. Wir im Boot-Prozess vor allen anderen Middlewares aufgerufen.
 *  
 */
class NnrestapiRequestParser implements MiddlewareInterface {
	
	/**
	 * RequestMethods, die zusätzlich geparsed werden sollen.
	 * In `Configuration/Middlewares.php` registriert.
	 * 
	 * @var array
	 */
	private $requestMethodsToParse = ['PUT', 'PATCH'];

	/**
	 * ## Handler aus `Configuration/Middlewares.php`
	 * 
	 * Entfernt/leert Stream aus Request, damit `$request->getBody()->getContents()` in 
	 * `\Nng\Nnrestapi\Mvc\Request::__construct()` einen Fallback auf `$request->getParsedBody()`
	 * macht. Dadurch wird das JSON im payload auch bei einem `PUT`-Request geparsed, der durch
	 * die Method `handler()` in den `$_POST`-Container geclont wurde. 
	 * 
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 * @return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

		if (in_array($request->getMethod(), $this->requestMethodsToParse)) {
			$request->getBody()->close();
		}

		return $handler->handle($request);
	}

	/**
	 * ## Handler aus `ext_localconf.php`
	 * 
	 * Nutzt externe Library, um den `multipart/form-data` des `PUT` und `PATCH` Requests zu parsen
	 * und in den `$_POST`-Container zu verschieben.
	 * 
	 * @return void
	 */
	public function handler() {

		// Ist es ein relevanter RequestType?
		$reqMethod = $_SERVER['REQUEST_METHOD'];
		if (!in_array($reqMethod, $this->requestMethodsToParse)) return;

		require_once( \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/typo3conf/ext/nnrestapi/Resources/Libraries/vendor/autoload.php' );

		// Variablen aus dem `multipart/form-data`-payload parsen und `$_FILES`-Container füllen
		$params = \Notihnio\RequestParser\RequestParser::parse();

		// Die Library nutzt lesbare KB-Angabe, aber Typo3 braucht Bytes als intval
		foreach ($_FILES as $k=>&$file) {
			$file['size'] = filesize( $file['tmp_name'] );
		}

		// Klon der Daten in einen Container, den Typo3 berücksichtigt
		foreach ($params->params as $k=>$v) {
			$_POST[$k] = $v;
		}

	}
	
}