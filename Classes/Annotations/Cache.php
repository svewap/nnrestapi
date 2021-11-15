<?php

namespace Nng\Nnrestapi\Annotations;

/**
 * ## Api\Cache
 * 
 * Enable full caching of the result.
 * Useful, if static data should be loaded like settings or country-lists etc.
 * The cache will only be cleared if the "clear cache" button is klicked in the backend. 
 *  
 * ```
 * @Api\Cache
 * ```
 * 
 * @Annotation
 */
class Cache
{
	public $value;

	public function __construct( $value ) {
		$this->value = true;
	}

	public function mergeDataForEndpoint( &$data ) {
		$data['cache'] = $this->value;
	}
}