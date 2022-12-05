<?php
declare(strict_types=1);

namespace Nng\Nnrestapi\Annotations\Security;

/**
 * ## Api\Security\CheckLocked
 * 
 * Check if IP of the request was locked.
 * 
 * ```
 * @Api\Security\CheckLocked()
 * 
 * // you can lock the current IP by calling:
 * \nn\rest::Security()->lockIp();
 * 
 * // and unlock it with:
 * \nn\rest::Security()->unlockIp();
 * ```
 * 
 * @Annotation
 */
class CheckLocked
{
	public $value;

	/**
     * This method is called when parsing all classes.
     *
     */
	public function mergeDataForEndpoint( &$data )
	{
		if (!isset($data['security'])) $data['security'] = [];
		$data['security']['checkLocked'] = 1;
	}

}