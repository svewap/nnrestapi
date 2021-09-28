<?php 

namespace Nng\Nnrestapi\Utilities;

/**
 * Helper zum Senden von Headern
 * 
 */
class Header extends \Nng\Nnhelpers\Singleton {

	/**
	 * Header senden, die allgemein für jeden Response wichtig sind.
	 * 
	 * Legt fest, von welcher Domain aus die Api aufgerufen werden darf, welche Request-Methods erlaubt sind
	 * und wie mit dem Cache umzugehen ist.
	 * 
	 * ```
	 * \nn\rest::Header()->sendControls();
	 * ```
	 * 
	 * @return void
	 */
	public function sendControls() {
		$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
		header("Access-Control-Allow-Origin: ${origin}");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Access-Control-Allow-Headers, Content-Type, Authorization");
		header("Access-Control-Allow-Credentials: true");
		header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS");
		header("Allow: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS");
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Access-Control-Allow-Headers: ' . ($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? 'origin, x-requested-with, content-type, cache-control'));

		return $this;
	}

	/**
	 * Sende json header
	 * ```
	 * \nn\rest::Header()->sendContentType();
	 * ```
	 * @return self
	 */
	public function sendContentType( $type = 'application/json' ) {
		header("Content-Type: {$type}");
		return $this;
	}

}