<?php

namespace Nng\Nnrestapi\Annotations;

/**
 * ## Api\Localize
 * 
 * Enable / disable localization for individual endpoints / methods.
 * 
 * ```
 * // force localization, even if `settings.localization.enabled = 0`
 * @Api\Localize(TRUE)
 * @Api\Localize()
 * 
 * // disable localization, even if `settings.localization.enabled = 1`
 * @Api\Localize(FALSE)
 * ```
 * 
 * @Annotation
 */
class Localize
{
	public $value;

	public function __construct( $arr ) {
		$val = ($arr['value'] ?? true) === false ? false : true;
		$this->value = $val;
	}

	public function mergeDataForEndpoint( &$data ) {
		$data['localize'] = $this->value;
	}
}