<?php 

namespace Nng\Nnrestapi\Utilities;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Helper für häufig genutzte Api-Funktionen.
 * 
 */
class Api implements SingletonInterface {


	/**
	 * Cache für die apiRootPageUid 
	 * @var int
	 */
	protected $apiRootPageUidCache;

	/**
	 * Absoluten Link zur Api generieren.
	 * Parameter können einzeln in der Reihenfolge der Methode übergeben werden - oder als assoziatives Array
	 * Beispiele:
	 * ```
	 * \nn\rest::Api()->uri( 'test' );
	 * \nn\rest::Api()->uri( 'test', ['type'=>20210904] );
	 * \nn\rest::Api()->uri( 'test', ['a'=>1, 'b'=>2] );
	 * \nn\rest::Api()->uri( ['controller'=>'test', 'action'=>'index', ...], ['a'=>1, 'b'=>2, ...] );
	 * \nn\rest::Api()->uri( $controller, $action, $uid, $param1, $param2, $additionalParams );
	 * ```
	 * @return string
	 */
	public function uri( $controller = 'index', $action = 'index', $uid = null, $param1 = null, $param2 = null, $additionalParams = [] ) {
		$argsOrder = ['controller', 'action', 'uid', 'param1', 'param2', 'additionalParams'];
		
		$mergedArgs = [];
		foreach (func_get_args() as $n=>$val) {
			$key = $argsOrder[$n];
			$arr = is_array($val) ? $val : [$key=>$val];
			$mergedArgs = array_merge( $mergedArgs, $arr );
		}

		if (!$mergedArgs['type']) {
			$mergedArgs['type'] = 20210904;
		}

		$pageUid = $this->getApiPageUid();
		$uri = \nn\t3::Page()->getLink( $pageUid, $mergedArgs, true );

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