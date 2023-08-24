<?php
declare(strict_types = 1);

namespace Nng\Nnrestapi\Controller;

use \Nng\Nnrestapi\Exception\PropertyValidationException;
use \Nng\Nnrestapi\Error\ApiError;

use TYPO3\CMS\Core\Controller\FileDumpController as CoreFileDumpController;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FileDumpController
 * 
 */
class FileDumpController extends CoreFileDumpController
{	
	protected ResourceFactory $resourceFactory;
    protected EventDispatcherInterface $eventDispatcher;
    protected ResponseFactoryInterface $responseFactory;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ResourceFactory $resourceFactory,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->resourceFactory = $resourceFactory;
        $this->responseFactory = $responseFactory;
    }

	/**
     * Main method to dump a file
	 * 
	 * ```
     * /index.php?eID=dumpEncFile&cnf=default&f=392&token=388c95839606f37c40bc547ebec867dd3d66cd92
	 * ```
	 * 
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     */
    public function dumpAction(ServerRequestInterface $request): ResponseInterface
    {
		$parameters = $this->buildParametersFromRequest( $request );

		$settings = \nn\t3::Settings()->get('nnrestapi');
		$configuration = $settings['fileUploadEncrypt'][$parameters['cnf'] ?? ''] ?? [];

		if (!$configuration) {
			\nn\rest::ApiError("Access denied. Invalid configuration.", 403, 403900 );
		}

		if (!$parameters['f'] || !$this->isTokenValid( $parameters, $request )) {
			$token = '';
			// $token = $this->getToken($parameters);
			\nn\rest::ApiError("Access denied. Invalid token. {$token}", 403, 403900 );
		}

		$file = $this->createFileObjectByParameters( $parameters );		
		if (!$file) {
			return $this->responseFactory->createResponse(404);
		}

		\nn\t3::debug( $file );

		if ($class = $configuration['encryptionClass'] ?? false) {
			$encryptionClass = new $class( $configuration );
			//$encryptionClass->decrypt( $sourcePath, $destPath );
		}

		die('OK');
	}

	protected function buildParametersFromRequest( ServerRequestInterface $request ): array
	{
		$parameters = parent::buildParametersFromRequest( $request );
		$queryParams = $request->getQueryParams();
		$parameters['eID'] = 'dumpEncFile';
		$parameters['cnf'] = $queryParams['cnf'] ?? '';
		return $parameters;
	}

	/**
	 * Create a token
	 * 
	 * @return string
	 */
	protected function getToken(array $parameters ): string
    {
		return GeneralUtility::hmac(implode('|', $parameters), 'resourceStorageDumpFile');
    }

}
