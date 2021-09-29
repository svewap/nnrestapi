<?php

namespace Nng\Nnrestapi\Service;

class EnvironmentService {

	/**
	 * Checks, if the RouteEnhancer exists.
	 * ```
	 * \Nng\Nnrestapi\Service\EnvironmentService::enhancerExists();
	 * ```
	 * @return bool
	 */
	public static function enhancerExists() {
		return strpos(\nn\rest::Api()->uri(['controller'=>'controller', 'action'=>'action' ]), '&type=') === false;
	}
	
	/**
	 * Checks, if the AUTH-Rewrite exists in the .htaccess
	 * ```
	 * \Nng\Nnrestapi\Service\EnvironmentService::rewriteCondExists();
	 * ```
	 * @return bool
	 */
	public static function rewriteCondExists() {
		$htaccess = preg_replace('/#(.*)/i', '', \nn\t3::File()->read('.htaccess') );
		return strpos( $htaccess, '{HTTP:Authorization}') !== false;
	}

}
