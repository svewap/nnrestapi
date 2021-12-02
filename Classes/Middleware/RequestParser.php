<?php
declare(strict_types = 1);

namespace Nng\Nnrestapi\Middleware;

use Exception;
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
class RequestParser implements MiddlewareInterface {
	
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
	 * ## Handler for HTTP-requests
	 * 
	 * This method is called from the `ext_localconf.php` of this extension.
	 * As all `ext_localconf.php`-scripts are included before TYPO3 starts its bootstrap process,
	 * this handler can take care of "preparing"  the HTTP-Request-Data for further parsing
	 * by the Core.
	 * 
	 * Uses an external library, to parse the `multipart/form-data` of the `PUT` and `PATCH`
	 * and transfer it to a `$_POST`-container.
	 * 
	 * If you are sending a `PUT` oder `PATCH` request and are only receiving a truncated part (e.g. 8192 bytes) 
	 * or are getting this error message: 
	 * ```
	 * Deprecated: TYPO3\CMS\Core\Database\ConnectionPool can not be injected/instantiated during 
	 * ext_localconf.php/TCA/ext_tables.php loading. Use lazy loading instead.
	 * ```
	 * Try checking the following things:
	 * - are there problems writing to the PHP-`/tmp` folder?
	 * - is there a `post_max_size` limit set? 
	 * 
	 * @return void
	 */
	public function handler() {

		// Ist es ein relevanter RequestType?
		$reqMethod = $_SERVER['REQUEST_METHOD'];
		if (!in_array($reqMethod, $this->requestMethodsToParse)) return;
		
		// Check if there was a problem with the content-length, e.g. stream or tmp-file could not be written
		$expectedLength = intval($_SERVER["CONTENT_LENGTH"]);
		$rawInput = file_get_contents('php://input', false, stream_context_get_default(), 0, $expectedLength);
		$realLength = strlen( $rawInput );

		if ($realLength < $expectedLength) {
			throw new Exception('There seems to be a problem with the multipart-formdata - less bytes were reveived than expected. Is the `/tmp` folder writeable?');
		}

		// Check, if multipart form-data was passed
		$isMultipartFormData = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;

		// No mutipart form-data? Then we don't need to specially parse the request
		if (!$isMultipartFormData) {
			$_POST['json'] = $rawInput;
			return;
		}

		// Multipart form-data? This needs special parsing to get the $_FILES in a PUT or PATCH request
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