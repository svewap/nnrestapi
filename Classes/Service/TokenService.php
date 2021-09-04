<?php

namespace Nng\Nnrestapi\Service;

class TokenService {

	/**
	 * Parses a JWT token.
	 * Returns payload if signature could be verrified.
	 * ```
	 * \Nng\Nnrestapi\Service\TokenService::parse('adhjdf.fsdfkjds.HKdfgfksfdsf');
	 * ```
	 * @param string $token
	 * @return array|false
	 */
	public static function parse ( $token = '' ) {
		$parts = explode('.', $token);
		$header = json_decode(base64_decode( array_shift($parts)), true);
		$payload = json_decode(base64_decode( array_shift($parts)), true);
		$signature = base64_decode(array_shift($parts));
		
		$checkSignature = self::createSignature($header, $payload);
		if ($signature !== $checkSignature) return FALSE;
		$payload['token'] = $token;
		
		return $payload;
	}

	/**
	 * Creates a signature for a JWT token
	 * ```
	 * \Nng\Nnrestapi\Service\TokenService::createSignature(['alg'=>'HS256', 'typ'=>'JWT'], ['test'=>123]);
	 * ```
	 * @param array $header
	 * @param array $payload
	 * @return string
	 */
	public static function createSignature ( $header = [], $payload = [] ) {
		return hash_hmac(
			'sha256',
			base64_encode(json_encode($header)) . '.' . base64_encode(json_encode($payload)), 
			\nn\t3::Environment()->getLocalConf('BE.installToolPassword')
		);
	}

	/**
	 * Creates a JWT token
	 * ```
	 * \Nng\Nnrestapi\Service\TokenService::create(['test'=>123]);
	 * ```
	 * @param array $payload
	 * @return string
	 */
	public static function create ( $payload = [] ) {
		$header = [
			'alg' => 'HS256',
			'typ' => 'JWT',
		];
		$signature = self::createSignature($header, $payload);
		return join('.', [
			base64_encode(json_encode($header)),
			base64_encode(json_encode($payload)),
			base64_encode($signature)
		]);
	}
	
	/**
	 * Get JWT token from request
	 * ```
	 * \Nng\Nnrestapi\Service\TokenService::getFromRequest();
	 * ```
	 * @return string
	 */
	public static function getFromRequest () {
		return self::parse(self::getBearerToken());
	}

	/** 
	 * Get header authorization
	 * 
	 * Wichtig: Wenn das hier nicht funktioniert, fehlt in der .htaccess 
	 * wahrscheinlich folgende Zeile:
	 * ```
	 * # nnrestapi: Verwenden, wenn PHP im PHP-CGI-Mode ausgeführt wird
	 * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
	 * ```
	 * 
	 * @return string
	 */
	public static function getAuthorizationHeader(){
		
		$headers = null;
		if (isset($_SERVER['Authorization'])) {
			$headers = trim($_SERVER['Authorization']);
		} else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
		} elseif (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			foreach ($requestHeaders as $k=>$v) {
				$requestHeaders[ucwords($k)] = $v;
			}
			if (isset($requestHeaders['Authorization'])) {
				$headers = trim($requestHeaders['Authorization']);
			}
		}
		return $headers;
	}

	/**
	 * Get access token from header
	 * ```
	 * \Nng\Nnrestapi\Services\AuthentificationService::getBearerToken();
	 * ```
	 * @return string|null
	 */
	public static function getBearerToken() {
		$headers = self::getAuthorizationHeader();
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}


}
