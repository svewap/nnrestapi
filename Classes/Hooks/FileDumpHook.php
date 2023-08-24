<?php

namespace Nng\Nnrestapi\Hooks;

use TYPO3\CMS\Core\Resource\Event\ModifyFileDumpEvent;
use Psr\Http\Message\ResponseFactoryInterface;
use TYPO3\CMS\Core\Security\JwtTrait;

/**
 * 
 */
class FileDumpHook 
{
	use JwtTrait;

	/**
	 * Hook called in `/sysext/core/Classes/Authentication/AbstractUserAuthentication.php`.
	 * It was Registered in `ext_localconf.php`.
	 * 
	 * @param ModifyFileDumpEvent $event 
	 * @param array $settings
	 * @param ResponseFactoryInterface $responseFactory
	 * @return void
	 */
	public function modifyFileDump( $event, $settings = [], $responseFactory = null ) 
	{
		$accessGroups = \nn\t3::Arrays($settings['accessGroups'] ?? '')->trimExplode();

		$request = $event->getRequest();

		if (!$accessGroups) {
			return;
		}

		if (in_array('fe_users', $accessGroups)) {
			$jwt = \nn\t3::Request()->getJwt();
			if ($jwt) {
				return;
			}
			if (\nn\t3::FrontendUser()->isLoggedIn( $request )) {
				return;
			}
		}
		
		if (in_array('be_users', $accessGroups)) {
			if (\nn\t3::BackendUser()->isLoggedIn( $request )) {
				//return;
			}			
		}

		$event->setResponse(
			$responseFactory->createResponse(403)
		);
	}
}