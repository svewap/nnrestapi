<?php 

namespace Nng\Nnrestapi\Utilities;

use \TYPO3\CMS\Core\Http\Response;

/**
 * Helper zum Senden von Headern
 * 
 */
class Header extends \Nng\Nnhelpers\Singleton {

	/**
	 * Response mit Headern anreichern, die allgemein für jeden Response wichtig sind.
	 * 
	 * Legt fest, von welcher Domain aus die Api aufgerufen werden darf, welche Request-Methods erlaubt sind
	 * und wie mit dem Cache umzugehen ist.
	 * 
	 * ```
	 * \nn\rest::Header()->addControls( $response );
	 * ```
	 * 
	 * @param Response $response
	 * @return void
	 */
	public function addControls( Response &$response = null ) {

		$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

		$response = $response->withHeader('Access-Control-Allow-Origin', $origin)
			->withHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Access-Control-Allow-Headers, Content-Type, Authorization')
			->withHeader('Access-Control-Allow-Credentials', 'true')
			->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS')
			->withHeader('Allow', 'GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS')
			->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
			->withHeader('Cache-Control', 'post-check=0, pre-check=0, false')
			->withHeader('Pragma', 'no-cache')
			->withHeader('Access-Control-Allow-Headers', $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? 'origin, x-requested-with, content-type, cache-control');

		return $this;
	}

	/**
	 * Response mit JSON header anreichern
	 * ```
	 * \nn\rest::Header()->addContentType( $response );
	 * ```
	 * @param Response $response
	 * @return self
	 */
	public function addContentType( Response &$response, $type = 'application/json; charset=utf-8' ) {
		$response = $response->withHeader('Content-Type', $type);
		return $this;
	}

	/**
	 * Echo header for 500 errors.
	 * ```
	 * \nn\rest::Header()->exception( 'message' );
	 * ```
	 * @return self 
	 */
	public function exception( $message = '', $code = 500 ) {
		$protocoll = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
		$phpSapiName = substr(php_sapi_name(), 0, 3);
		
		$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
		header('Access-Control-Allow-Origin: ' . $origin);
		header('Access-Control-Allow-Credentials: true');

		if ($phpSapiName == 'cgi' || $phpSapiName == 'fpm') {
			header('Status: ' . $code . ' ' . $message);
		} else {
			header("{$protocoll} {$code} {$message}");
		}
		return $this;
	}
}