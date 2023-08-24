<?php
declare(strict_types = 1);

namespace Nng\Nnrestapi\Resource\EventListener;

use TYPO3\CMS\Core\Resource\Event\ModifyFileDumpEvent;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * ## ModifyFileDumpEventListener
 * 
 * Registered in `Configuration/Services.yaml`
 * @see https://docs.typo3.org/m/typo3/reference-coreapi/12.4/en-us/ApiOverview/Events/Events/Core/Resource/ModifyFileDumpEvent.html
 * 
 */
final class ModifyFileDumpEventListener
{
    protected ResponseFactoryInterface $responseFactory;

    public function __construct(
        ResponseFactoryInterface $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

	public function __invoke(ModifyFileDumpEvent $event): void
    {
		$file = $event->getFile();
		$storage = $file->getStorage()->getStorageRecord();
	
		$conf = $storage['nnrestapi_config'] ?? false;
		if (!$conf) return;

		$settings = \nn\t3::Settings()->get('nnrestapi')['sysFileStoragePresets'][$conf] ?? [];
		$className = $settings['className'] ?? false;
		if (!$className) return;

		$classInstance = new $className();
		$classInstance->modifyFileDump( $event, $settings, $this->responseFactory );
    }
}
