<?php

namespace Nng\Nnrestapi\Annotations;

/**
 * ## Api\MaxAge
 * 
 * Send `Cache-Control: max-age={time}` header.
 *  
 * ```
 * @Api\MaxAge( 60 )
 * ```
 * 
 * @Annotation
 */
class MaxAge
{
	public $value;

	public function __construct( $value ) {
		$this->value = $value['value'] ?? 60;
	}

	public function mergeDataForEndpoint( &$data ) {
		$data['maxAge'] = $this->value;
	}
}