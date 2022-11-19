<?php
declare(strict_types=1);

namespace Nng\Nnrestapi\Annotations\Security;

/**
 * ## Api\Security\MaxRequestsPerMinute
 * 
 * Limits the number of request from a certain IP
 * 
 * ```
 * // defaults to max of 60 request per minute, no matter which endpoint is called
 * @Api\Security\MaxRequestsPerMinute()
 * 
 * // max of 10 request per minute, no matter which endpoint is called
 * @Api\Security\MaxRequestsPerMinute( 10 )
 * 
 * // max of 10 request per minute for given ID
 * @Api\Security\MaxRequestsPerMinute( 10, "some_ID" )
 * ```
 * 
 * @Annotation
 */
class MaxRequestsPerMinute
{
	public $value;

	/**
     * Normalize parameter to array.
	 * 
     * @return void
     */
	public function __construct( $arr )
	{
	   $this->value = is_array( $arr['value'] ?? null ) ? $arr['value'] : [$arr['value'] ?? 60, 'all'];
	}

	/**
     * This method is called when parsing all classes.
     *
     */
	public function mergeDataForEndpoint( &$data )
	{
		if (!isset($data['security'])) $data['security'] = [];
		[$max, $id] = $this->value;
		$data['security']['maxRequestsPerMinute'][$id] = $max;
	}

}