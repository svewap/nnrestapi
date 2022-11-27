<?php 

namespace Nng\Nnrestapi\Utilities;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Helper für häufig genutzte Api-Funktionen.
 * 
 */
class Api extends \Nng\Nnhelpers\Singleton {


	/**
	 * Cache für die apiRootPageUid 
	 * @var int
	 */
	protected $apiRootPageUidCache;

	/**
	 * endpoint
	 * @var string
	 */
	protected $endpoint;


	public function __construct( $endpoint = null ) {
		$this->endpoint = $endpoint;
	}

	/**
	 * Absoluten Link zur Api generieren.
	 * 
	 * Parameter können einzeln in der Reihenfolge der Methode übergeben werden - oder als assoziatives Array
	 * Beispiele:
	 * ```
	 * \nn\rest::Api('nnrestapi')->uri( 'test' );
	 * \nn\rest::Api()->uri( 'test', ['ext'=>'nnrestapi'] );
	 * 	
	 * \nn\rest::Api()->uri( 'test' );
	 * \nn\rest::Api()->uri( 'test', ['type'=>20210904] );
	 * 
	 * \nn\rest::Api()->uri( 'test', ['a'=>1, 'b'=>2] );
	 * \nn\rest::Api()->uri( ['ext'=>'nnrestapi', 'controller'=>'test', 'action'=>'index', ...], ['a'=>1, 'b'=>2, ...] );
	 * \nn\rest::Api()->uri( $controller, $action, $uid, $param1, $param2, $additionalParams );
	 * ```
	 * @return string
	 */
	public function uri( $controller = 'index', $action = 'index', $uid = null, $param1 = null, $param2 = null, $param3 = null, $additionalParams = [] ) {

		$argsOrder = ['controller', 'action', 'uid', 'param1', 'param2', 'param3', 'additionalParams'];
		
		$mergedArgs = [
			'ext' => $this->endpoint ?: null
		];

		foreach (func_get_args() as $n=>$val) {
			$key = $argsOrder[$n];
			$arr = is_array($val) ? $val : [$key=>$val];
			$mergedArgs = array_merge( $mergedArgs, $arr );
		}

		$defaultEndpoint = \nn\rest::Endpoint()->find( true, $mergedArgs['controller'], $mergedArgs['action'] );

		// `param2` wird zu `param3` verschoben, `controller` wird zu `action` etc.
		if ($mergedArgs['ext'] ?? false) {
						
			if ($mergedArgs['ext'] != ($defaultEndpoint['slug'] ?? '')) {
				$argsToShift = array_reverse(array_slice($argsOrder, 0, -1));
				foreach ($argsToShift as $n=>$key) {
					if ($prevKey = $argsToShift[$n+1] ?? false) {
						$mergedArgs[$key] = $mergedArgs[$prevKey];
					}
				}
				$mergedArgs['controller'] = $mergedArgs['ext'];
				if ($mergedArgs['uid'] == 'index') {
					unset($mergedArgs['uid']);
				}
			}
		}
		unset($mergedArgs['ext']);

		foreach ($mergedArgs as $k=>$v) {
			if ($v == '') unset($mergedArgs[$k]);
		}

		if (($mergedArgs['action'] ?? '') == 'index') {
			unset($mergedArgs['action']);
		}

		$urlBase = \nn\t3::Environment()->getBaseUrl();
		$apiUrlPrefix = \nn\rest::Settings()->getApiUrlPrefix();

		$uriParts = [];
		if ($additionalParams['absolute'] ?? false) {
			$uriParts[] = $urlBase;
		}
		if ($apiUrlPrefix) {
			$uriParts[] = rtrim($apiUrlPrefix, '/');
		}
		$uriParts += $mergedArgs;

		$uri = join('/', $uriParts);

		return $uri;
	}

	/**
	 * Die `pageUid` für Seite mit Slug `/` holen.
	 * 
	 * Falls keine Seite ermittelt werden kann, aktuelle `pageUid` zurückgeben.
	 * Dient zum Generieren einer möglichst kurzen URL beim Aufruf von z.B. `uri()`
	 * ```
	 * \nn\rest::Api()->uri( $controller, $action, $uid, $param1, $param2 );
	 * ```
	 * @return string
	 */
	public function getApiPageUid() {

		if ($pageUid = $this->apiRootPageUidCache) {
			return $pageUid;
		}

		// rootPageId anhand des Slug-Pfades zu '/' ermitteln
		if ($rootPageRow = \nn\t3::Db()->findOneByValues('pages', ['slug'=>'/'])) {
			$pageUid = $rootPageRow['uid'];
		}

		// rootPageId anhand der yaml-siteconfig ermitteln
		if (!$pageUid) {
			$request = $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
			$site = $request->getAttribute('site');
			$pageUid = $site->getRootPageId();
		}

		return $this->apiRootPageUidCache = $pageUid;
	}


}