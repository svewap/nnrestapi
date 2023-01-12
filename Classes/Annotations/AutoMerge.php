<?php

namespace Nng\Nnrestapi\Annotations;

/**
 * ## Api\AutoMerge
 * 
 * Disable automatic merging of the JSON body with an existing model
 * when passing an uid in the request and using an model in the parameter.
 *  
 * ```
 * @Api\AutoMerge()
 * @Api\AutoMerge(TRUE)
 * @Api\AutoMerge(FALSE)
 * ```
 * 
 * @Annotation
 */
class AutoMerge
{
	public $value;

	public function __construct( $value ) {
		$this->value = ($value['value'] ?? true) == true ? true : false;
	}

	public function mergeDataForEndpoint( &$data ) {
		$data['autoMerge'] = $this->value;
	}
}