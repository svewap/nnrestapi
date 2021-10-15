<?php
namespace Nng\Nnrestapi\Routing\Enhancer;

use TYPO3\CMS\Core\Routing\Enhancer\AbstractEnhancer;
use TYPO3\CMS\Core\Routing\Enhancer\RoutingEnhancerInterface;
use TYPO3\CMS\Core\Routing\Route;
use TYPO3\CMS\Core\Routing\RouteCollection;

/**
 * 
 * ```
 * routeEnhancers:
 *   Nnrestapi:
 *     type: NnrestapiEnhancer
 * ```
 */
class NnrestapiEnhancer extends AbstractEnhancer implements RoutingEnhancerInterface
{
	public const ENHANCER_NAME = 'NnrestapiEnhancer';

	/**
	 * @var array
	 */
	protected $configuration;

	public function __construct(array $configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * {@inheritdoc}
	 */
	public function enhanceForMatching(RouteCollection $collection): void {
		$basePath = \nn\rest::Settings()->getApiUrlPrefix();
		$variant = clone $collection->get('default');
		$variant->setPath( $basePath . '{params?}');
		$variant->setRequirement('params', '.*');
		$collection->add('enhancer_' . $basePath . spl_object_hash($variant), $variant);
	}

	/**
	 * {@inheritdoc}
	 */
	public function enhanceForGeneration(RouteCollection $collection, array $parameters): void {
	}
}