<?php 

namespace Nng\Nnrestapi\Utilities;

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
	 * Constructor
	 * 
	 * @return void
	 */
	public function initialize() {
		
		$request = $this->request ?: $GLOBALS['TYPO3_REQUEST'];
		if (!$request) return;

		$siteIdentifier = '';
		$apiConfiguration = [];

		$site = $request->getAttribute('site');
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

}