<?php 

namespace Nng\Nnrestapi\Utilities;

use \TYPO3\CMS\Core\Http\Response;

/**
 * Helper zum Senden von Headern
 * 
 */
class Header extends \Nng\Nnhelpers\Singleton 
{
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

		$defaultHeaders = [
			'Access-Control-Allow-Headers' => $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? 'Origin, X-Requested-With, Content-Type, Authorization, Cache-Control',
			'Access-Control-Allow-Credentials' => 'true',
			'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS',
			'Allow' => 'GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS',
			'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0, false',
			'Pragma' => 'no-cache',
		];

		$headersFromSetup = \nn\rest::Settings()->get()['response']['headers'];

		// the `HTTP_REFERER` without full path, e.g. `https://www.mydomain.com` or `https://localhost:8090`
		$referer = \nn\rest::Access()->getRefererDomain();

		$origin = $_SERVER['HTTP_ORIGIN'] ?? $referer ?: '*';
		$allowedPatterns = $headersFromSetup['Access-Control-Allow-Origin'] ?? '*';
		$selfUrl = rtrim(\nn\t3::Environment()->getBaseURL(), '/');

		// check if current client meets pattern(s) defined in `Access-Control-Allow-Origin` from TypoScript
		$acceptOrigin = \nn\rest::Access()->domainIsAllowed($origin, $allowedPatterns) ?: $selfUrl;
		$headersFromSetup['Access-Control-Allow-Origin'] = $acceptOrigin;

		// merge headers from TypoScript with default headers
		$mergedHeaders = array_merge($defaultHeaders, $headersFromSetup);

		foreach ($mergedHeaders as $key=>$val) {
			if ($val = trim($val)) {
				$response = $response->withHeader($key, $val);
			}
		}
		
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
	 * Add a custom header or override an existing one
	 * ```
	 * \nn\rest::Header()->add( $response, 'Cache-Control', 'max-age=10' );
	 * ```
	 * @param Response $response
	 * @param string $key
	 * @param string $value
	 * @return self
	 */
	public function add( Response &$response, $key = '', $value = '' ) 
	{
		$response = $response->withHeader($key, $value);
		return $this;
	}
	
	/**
	 * Remove a header
	 * ```
	 * \nn\rest::Header()->remove( $response, 'Pragma' );
	 * ```
	 * @param Response $response
	 * @param string $key
	 * @return self
	 */
	public function remove( Response &$response, $key = '' ) 
	{
		$response = $response->withoutHeader($key);
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