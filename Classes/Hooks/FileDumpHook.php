<?php

namespace Nng\Nnrestapi\Hooks;

use TYPO3\CMS\Core\Resource\Event\ModifyFileDumpEvent;
use Psr\Http\Message\ResponseFactoryInterface;
use TYPO3\CMS\Core\Security\JwtTrait;

/**
 * # Hook for checking access rights to files
 * 
 * The nnrestapi extends the TCA for the filemount: It adds a dropdown allowing
 * you to select the configurations defined in 
 * `plugin.tx_nnrestapi.settings.sysFileStoragePresets`.
 * 
 * This way you can restrict the access to files so only fe_users or be_users
 * can view / download them.
 * 
 * To use this feature, you must create a filemount that is located OUTSIDE of
 * the public accessible webroot. (`fileadmin` or the webroot where the index.php
 * is located are not an option!).
 * 
 * Why? Let's Deep-Dive:
 * 
 * When a filemount is set outside of the public accessible folder (e.g. by
 * using an absolute path), TYPO3 will create URLs using the `eID=dumpFile`.
 * 
 * The eID-request is processed by `\TYPO3\CMS\Core\Controller\FileDumpController`
 * which triggers the `ModifyFileDumpEvent` before sending the file to the
 * browser.
 * 
 * This Event allows checking access rights to the file that was requested by
 * the frontend.
 * 
 */
class FileDumpHook 
{
	use JwtTrait;

	/**
	 * Hook called by `\TYPO3\CMS\Core\Controller\FileDumpController`.
	 * Registered in `EXT:nnrestapi/Configuration/Services.yaml`
	 * 
	 * Must return `void` if access is granted.
	 * Must enrich the $response with a 403 if the access is forbidden.
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
				return;
			}			
		}

		$event->setResponse(
			$responseFactory->createResponse(403)
		);
	}
}