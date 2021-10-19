<?php

namespace Nng\Nnrestapi\Annotations;

/**
 * ## Api\IncludeHidden
 * 
 * Enable retrieving of hidden records and relations from Database.
 * 
 * This makes an Endpoint behave like the Typo3 Backend: Hidden records
 * and records with `fe_group` or `starttime/endtime`-restrictions will
 * be returned to frontend.
 *  
 * ```
 * @Api\IncludeHidden
 * ```
 * 
 * @Annotation
 */
class IncludeHidden
{
	public $value;

	public function __construct( $value ) {
		$this->value = true;
	}

	public function mergeDataForEndpoint( &$data ) {
		$data['includeHidden'] = $this->value;
	}
}