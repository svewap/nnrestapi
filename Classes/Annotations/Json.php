<?php

namespace Nng\Nnrestapi\Annotations;

/**
 * ## Api\Json
 * 
 * Simple options and settings while converting the response-data to JSON.
 * 
 * ```
 * // when converting object or model: return a max depth of 4 levels (default is 10)
 * @Api\Json(depth=4)
 * ```
 * 
 * @Annotation
 */
class Json
{
	public $value;

	public function __construct( $value ) {
		$this->value = $value;
	}

	public function mergeDataForEndpoint( &$data ) {
		$data['json'] = $this->value;
	}
}