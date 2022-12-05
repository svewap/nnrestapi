<?php
declare(strict_types=1);

namespace Nng\Nnrestapi\Annotations\Security;

/**
 * ## Api\Security\CheckInjections
 * 
 * Check for typical SQL-injection-patterns in request-body
 * 
 * ```
 * @Api\Security\CheckInjections() 
 * ```
 * 
 * @Annotation
 */
class CheckInjections
{
	public $value;

	/**
     * This method is called when parsing all classes.
     *
     */
	public function mergeDataForEndpoint( &$data )
	{
		if (!isset($data['security'])) $data['security'] = [];
		$data['security']['checkInjections'] = !($this->value === false);
	}

}