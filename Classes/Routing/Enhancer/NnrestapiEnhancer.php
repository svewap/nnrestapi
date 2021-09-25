<?php

namespace Nng\Nnrestapi\Routing\Enhancer;

use Nng\Nnrestapi\Routing\Enhancer\VariableProcessor;

use TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Routing\Route;
use TYPO3\CMS\Core\Routing\RouteCollection;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Wandelt die GET-Parameter in schöne, lesbare Pfade um – und löst Pfade in GET-Parameter auf.
 * In weiten Teilen ein Klon des `\TYPO3\CMS\Core\Routing\Enhancer\SimpleEnhancer`.
 * 
 */
class NnrestapiEnhancer extends \TYPO3\CMS\Core\Routing\Enhancer\SimpleEnhancer
	implements 
		\TYPO3\CMS\Core\Routing\Enhancer\RoutingEnhancerInterface, 
		\TYPO3\CMS\Core\Routing\Enhancer\InflatableEnhancerInterface, 
		\TYPO3\CMS\Core\Routing\Enhancer\ResultingInterface
{
	/**
	 * @var array
	 */
	protected $configuration;

	/**
     * @var VariableProcessor
     */
    protected $variableProcessor;

	/**
	 * `typeNum`, der für die API verwendet wird. Muss dem Wert im `setup.typoscript` entsprechen.
	 * Kann in er yaml RouteEnhancer-Konfiguration überschrieben werden.
	 * 
	 * @var int
	 */
	protected $defaultLimitToPageType = 20210904;

	/**
	 * Constructor
	 * 
	 */
	public function __construct(array $configuration)
	{
		$this->configuration = $configuration;
	}

	/**
	 * Überschreibt die `buildResult()` Methode des SimpleEnhancers.
	 * Ziel: `cHash` entfernen, um die URL einfacher und lesbarer zu halten.
	 * 
	 * @return \TYPO3\CMS\Core\Routing\PageArguments
	 */
	public function buildResult(Route $route, array $results, array $remainingQueryParameters = []): PageArguments
	{
		$variableProcessor = $this->getVariableProcessor();
		$parameters = array_intersect_key( $results, array_flip($route->compile()->getPathVariables()) );
		$routeArguments = $variableProcessor->inflateParameters($parameters, $route->getArguments());

		$staticArguments = $routeArguments;

		$page = $route->getOption('_page');
		$pageId = (int)(isset($page['t3ver_oid']) && $page['t3ver_oid'] > 0 ? $page['t3ver_oid'] : $page['uid']);
		$pageId = (int)($page['l10n_parent'] > 0 ? $page['l10n_parent'] : $pageId);

		if ($page['MPvar'] ?? '') {
			$routeArguments['MP'] = $page['MPvar'];
		}
		$type = $this->resolveType($route, $remainingQueryParameters);
		return new \TYPO3\CMS\Core\Routing\PageArguments($pageId, $type, $routeArguments, $staticArguments, $remainingQueryParameters);	
	}

	/**
	 * Gibt eine Variante der URL zurück für die gegebene `pageRoute`.
	 *
	 * @param Route $defaultPageRoute
	 * @param array $configuration
	 * @return Route
	 */
	protected function getVariant(Route $defaultPageRoute, array $configuration): Route
	{
		$typeNum = $this->configuration['limitToPageType'] ?? $this->defaultLimitToPageType;

		$variant = parent::getVariant( $defaultPageRoute, $configuration );

	 	$variant->setOption( '_decoratedParameters', ['type'=>$typeNum]);
		return $variant;
	}

	/**
	 * Generiert eine schöne, lesbare URL anhand der übergebenen Parameter.
	 * Wird bei jedem TypoLink oder `f:link` aufgerufen.
	 * 
	 * {@inheritdoc}
	 */
	public function enhanceForGeneration(RouteCollection $collection, array $parameters): void
	{
		$typeNum = $this->configuration['limitToPageType'] ?? $this->defaultLimitToPageType;

		// Wenn `pageNum` / `type` sich nicht an die API richtet: Abbruch
		if (($parameters['type'] ?? false) != $typeNum) return;

		$defaultPageRoute = $collection->get('default');
		$variant = $this->getVariant($defaultPageRoute, $this->configuration);
		
		unset($parameters['type']);
		$deflatedParameters = $this->getVariableProcessor()->deflateParameters($parameters, $variant->getArguments());

		$variant->addOptions(['deflatedParameters' => $deflatedParameters]);
		$collection->add('enhancer_' . spl_object_hash($variant), $variant);
	}

	/**
     * @return VariableProcessor
     */
    protected function getVariableProcessor(): \TYPO3\CMS\Core\Routing\Enhancer\VariableProcessor
    {
        if (isset($this->variableProcessor)) {
            return $this->variableProcessor;
        }
        return $this->variableProcessor = new VariableProcessor();
    }


}
