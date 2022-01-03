<?php 

namespace Nng\Nnrestapi\Utilities;

use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Settings for the rest-api
 * 
 */
class Settings extends \Nng\Nnhelpers\Singleton {

	/**
	 * The current Request.
	 * @var \TYPO3\CMS\Core\Http\ServerRequest
	 */
	private $request;
	
	/**
	 * Site identifier (= Name of the siteConfig-yaml)
	 * @var string
	 */
	private $siteIdentifier = '';

	/**
	 * Api configuration (Array from the siteConfig-yaml)
	 * @var string
	 */
	private $apiConfiguration = [];
	
	/**
	 * Query settings
	 * @var array
	 */
	private $querySettings = [];

	/**
	 * Settings from TypoScript setup
	 * @var array
	 */
	private $typoscriptSettings = [];

	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function initialize() {
		
		$request = $this->request ?: $GLOBALS['TYPO3_REQUEST'] ?? false;
		if (!$request) return;

		$siteIdentifier = '';
		$apiConfiguration = [];

		$site = $request->getAttribute('site');

		// Fallback for TYPO3 v9
		if (!$site) {
			$site = \nn\t3::Environment()->getSite( $request );
		}

		$siteIdentifier = $site->getIdentifier();
		if (!is_a($site, \TYPO3\CMS\Core\Site\Entity\NullSite::class)) {
			$apiConfiguration = $site->getConfiguration()['nnrestapi'] ?? [];
		}

		$this->siteIdentifier = $siteIdentifier;
		$this->apiConfiguration = $apiConfiguration;
	}

	/**
	 * Sets the current Request.
	 * 
	 * This must be done BEFORE any other scripts try to access the Settings.
	 * Done in the `Authenticator`-MiddleWare of the nnrest-extension
	 * ```
	 * \nn\rest::Settings()->setRequest( $request );
	 * ```
	 * @param \TYPO3\CMS\Core\Http\ServerRequest
	 * @return array
	 */
	public function setRequest( $request ) {
		$this->request = $request;
		$this->initialize();
	}
	
	/**
	 * Return the configuration for `nnrestapi` from the siteConfig-YAML
	 * ```
	 * \nn\rest::Settings()->getConfiguration();
	 * \nn\rest::Settings()->getConfiguration( 'key' );
	 * \nn\rest::Settings()->getConfiguration( 'some.deep.path' );
	 * ```
	 * @return array
	 */
	public function getConfiguration( $path = '' ) {
		if (!$path) return $this->apiConfiguration;
		return \nn\t3::Settings()->getFromPath( $path, $this->apiConfiguration );
	}
	
	/**
	 * Return the TypoScript setup for `plugin.tx_nnrestapi.settings`
	 * ```
	 * \nn\rest::Settings()->get();
	 * ```
	 * @return array
	 */
	public function get() {
		if ($cache = $this->typoscriptSettings) {
			return $cache;
		}
		return $cache = \nn\t3::Settings()->get('nnrestapi');
	}

	/**
	 * Return the site-identfier = the name of the siteConfig-YAML
	 * ```
	 * \nn\rest::Settings()->getSiteIdentifier();
	 * ```
	 * @return array
	 */
	public function getSiteIdentifier() {
		return $this->siteIdentifier;
	}
	
	/**
	 * Get URL-path-prefix used for all calls to the RestAPI. 
	 * Default is `/api/`.
	 * ```
	 * \nn\rest::Settings()->getApiUrlPrefix();
	 * ```
	 * @return string
	 */
	public function getApiUrlPrefix() {
		$basePath = $this->apiConfiguration['routing']['basePath'] ?? '/api';
		return '/' . trim($basePath, '/') . '/';
	}


	/**
	 * ```
	 * \nn\rest::Settings()->getQuerySettings();
	 * \nn\rest::Settings()->getQuerySettings('ignoreEnableFields');
	 * ```
	 * @return  array
	 */
	public function getQuerySettings( $field = '' ) {
		return $field ? ($this->querySettings[$field] ?? '') : $this->querySettings;
	}

	/**
	 * Enable retrieving of hidden records and relations in the Frontend.
	 * Solved by Xclass-ing the Core `HiddenRestriction` and `QueryFactory`.
	 * See `Nng\Nnrestapi\Xclass\QueryFactory` and `Nng\Nnrestapi\Xclass\HiddenRestriction`.
	 * 
	 * Probably there is a better solution - spent too much time searching.
	 * 
	 * ```
	 * \nn\rest::Settings()->setIgnoreEnableFields( true );
	 * ```
	 * @param   array  $querySettings  Query settings
	 * @return  self
	 */
	public function setIgnoreEnableFields( $ignoreEnableFields = false ) {
		$this->querySettings['ignoreEnableFields'] = $ignoreEnableFields;
		return $this;
	}
}