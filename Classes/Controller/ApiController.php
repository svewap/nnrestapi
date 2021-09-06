<?php
namespace Nng\Nnrestapi\Controller;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Nnrestapi
 * 
 */
class ApiController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

	/**
	 * Die Reihenfolge, in der Endpoint-Parameter in der URL übergeben wurden.
	 * Kann mit `controller` oder dem `slug` beginnen
	 * `api		/{controller}	/{action}		/{uid}		/{param1}	/...
	 * `api		/{slug}			/{controller}	/{action}	/{uid}		/...
     * @var array
     */
    protected $pathSegmentOrder = ['controller', 'action', 'uid', 'param1', 'param2', 'param3'];

	/**
     * @var \Nng\Nnrestapi\Mvc\View\JsonView
     */
    protected $view;
	
    /**
     * @var string
     */
	protected $defaultViewObjectName = \Nng\Nnrestapi\Mvc\View\JsonView::class;


	/**
	 * Initialisierung des Requests.
	 * Lädt Bibliotheken und Framework für Request.
	 * 
	 * Prüft, ob JWT-Token übergeben wurde und authentifiziert den FE-User falls möglich.
	 * 
	 */
	public function initializeView( \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view ) {

		// autoload.php laden
		//\Nng\Nnrestapi\Service\AutoloadService::loadLibraries();

		$this->sendHeaders();

		// Falls kein Login über fe_user-Cookie passiert ist, Json Web Token (JWT) prüfen
		if (!\nn\t3::FrontendUser()->isLoggedIn()) {
			$tokenData = \Nng\Nnrestapi\Service\TokenService::getFromRequest();
			if ($uid = $tokenData['uid'] ?? false) {
				\nn\t3::FrontendUserAuthentication()->loginField( $tokenData['token'], 'nnrestapi_jwt' );
			}
		}

		$this->view->setVariablesToRender(['data']);
	}


	/**
	 * 	Basis-Endpoint für alle Requests an die REST-Api
	 * 	Übernimmt die Delegation an den entsprechenden Controller unter Classes/Api
	 * 
	 * 	Prüft, ob der User die Rechte hat, eine Methode aufzurufen.
	 * 	Wird über die Annotations der Klassen-Methoden gesteuert, z.B.
	 * 
	 * 	`@access public` -> Zugriff auch für nicht-feUser erlaubt	
	 *	
	 * 	Mit RouteEnhancing:
	 * 	GET /api/feed/			=>  Nng\Nnrestapi\Api\Feed->indexAction
	 * 	GET /api/feed/1			=>  Nng\Nnrestapi\Api\Feed->getEntryAction
	 * 	POST /api/feed/1		=>  Nng\Nnrestapi\Api\Feed->postEntryAction
	 * 	POST /api/feed/some/1	=>  Nng\Nnrestapi\Api\Feed->postSomeAction
	 * 
	 * 	Ohne RouteEnhancing:
	 * 	GET ?type=20200505&controller=feed						=> Nng\Nnrestapi\Api\Feed->indexAction
	 * 	GET ?type=20200505&controller=feed&uid=1				=> Nng\Nnrestapi\Api\Feed->getEntryAction
	 * 	POST ?type=20200505&controller=feed&uid=1				=> Nng\Nnrestapi\Api\Feed->postEntryAction
	 * 	POST ?type=20200505&controller=feed&action=some&uid=1	=> Nng\Nnrestapi\Api\Feed->postSomeAction
	 *	
	 */
	public function indexAction() {

		$httpMethod = $this->request->getMethod();
		$reqVars = \nn\t3::Request()->GP() ?? [];
		$payload = json_decode(file_get_contents('php://input'), true) ?: [];
		
		if (is_array($payload)) {
			$reqVars = \nn\t3::Arrays( $reqVars )->merge( $payload );
		}


		// Prüfen, ob erstes Pfadsegment der URL ein registrierter Slug war, z.B. `api/nnrestapi/test` statt `api/test`
		$endpoints = \nn\rest::Endpoint()->getAll();
		$controllerClassName = false;

		foreach ($endpoints as $endpoint) {
			if (strcasecmp($reqVars['controller'], $endpoint['slug']) == 0) {
				
				// `api/{slug}/{controller}/{action}` übergeben statt `api/{controller}/{action}?
				foreach ($this->pathSegmentOrder as $n=>$key) {

					// Dann Parameter verschieben: `action` enthält `controller`, `uid` enthält `action` etc.
					$nextVal = $reqVars[$this->pathSegmentOrder[$n+1]] ?? '';
					$reqVars[$key] = $nextVal;
				}

				$controllerClassName = rtrim($endpoint['namespace'], '\\') . '\\' . ucfirst( $reqVars['controller'] );
			} 
		}

		$controllerName = ucfirst($reqVars['controller'] ?? '');
		$actionName = $reqVars['action'] ?: false;
		$uid = $reqVars['uid'] ?: false;
		
		$reqType = 'get';

		switch ($httpMethod) {
			case 'HEAD':
				$reqType = 'head';
				break;
			case 'GET':
				$reqType = 'get';
				break;
			case 'POST':
				$reqType = 'post';
				break;
			case 'PUT':
				$reqType = 'put';
				break;
			case 'DELETE':
				$reqType = 'delete';
				break;
			case 'OPTIONS':
				return $this->noContent();
				break;
			default:
				return $this->success();
				//$this->error(400, "Bad Request. httpMethod {$httpMethod} not supported.");
		}

		if (is_numeric($actionName)) {
			$uid = $actionName;
			$actionName = false;
		}

		if ($actionName === false) {
			$actionName = 'index';
		}

		if ($uid) $reqVars['uid'] = $uid;

		// Methode, die aufgerufen werden soll, z.B. `getIndexAction` oder `postSometingAction`
		$methodName = lcfirst($reqType . ucfirst( $actionName ) . 'Action');
		
		// Über `\nn\rest::Endpoint()->register()` bekannten Controllername mit höchster Prio finden
		if (!$controllerClassName || !method_exists($controllerClassName, $methodName)) {
			foreach ($endpoints as $endpoint) {
				if ($namespace = $endpoint['namespace'] ?? false) {
					$className = rtrim($namespace, '\\') . '\\' . $controllerName;
					if (method_exists($className, $methodName)) {
						$controllerClassName = 	$className;
						break;
					}
				}
			}
		}

		// Methode und/oder Klasse existiert nicht. Fehlermeldung ausgeben
		if (!$controllerClassName || !method_exists($controllerClassName, $methodName)) {
			$checked = array_map( 
				function ( $str ) use ($controllerName, $methodName) {
					return "\\{$str}\\{$controllerName}->{$methodName}()";
				},
				\nn\t3::Arrays( $endpoints )->pluck('namespace')->toArray()
			);
			return $this->error(404, "Endpoint controller {$controllerName}->{$methodName}() not found. Checked these namespaces: " . join( ', ', $checked ) );
		}

		// Klasse instanziieren
		$classInstance = \nn\t3::injectClass( $controllerClassName );

		// Rechte prüfen.
		$ref = new \ReflectionMethod( $controllerClassName, $methodName );

		preg_match_all('#@(.*?)\n#s', $ref->getDocComment(), $rawAnnotations);
		$annotations = [];
		foreach ($rawAnnotations as $annotation) {
			$p = explode(' ', $annotation[0], 2);
			$annotations[trim($p[0])] = trim($p[1]);
		}

		// Nur wenn Annotation `@access public` exisitert wird action von Nicht-Fe-User erlaubt
		$access = \nn\t3::Arrays($annotations['@access'] ?? '')->trimExplode();
		if (!in_array('public', $access) && !\nn\t3::FrontendUser()->isLoggedIn()) {
			return $this->error(403, "{$controllerName}->{$methodName}() no public access. Please authenticate to access this endpoint or use @public annotation to mark the endpoint as public accessible." );
		}

		$result = $classInstance->{$methodName}( $reqVars, $payload, $methodName ) ?: [];
		$this->view->assign('data', $result);
	}

	/**
	 * Einen Fehler ausgeben
	 * 
	 * @return void
	 */
	public function error( $statusCode, $message = '' ) {
		$data = [
			'status' 	=> $statusCode,
			'message'	=> $message,
		];
		$this->throwStatus($statusCode, '', json_encode($data));
	}

	/**
	 * 200 OK
	 * 
	 * @return void 
	 */
	public function success( $message = '' ) {
		$this->error(200, $message ?: 'OK');
	}
	
	/**
	 * 204 No Content
	 * 
	 * @return void 
	 */
	public function noContent( $message = '' ) {
		$this->error(204, $message ?: 'No Content');
	}

	/**
	 * Header senden.
	 * 
	 */
	public function sendHeaders () {
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Access-Control-Allow-Headers, Content-Type, Authorization");
		header("Access-Control-Allow-Credentials: true");
		header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS");
		header("Allow: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS");
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Access-Control-Allow-Headers: ' . ($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? 'origin, x-requested-with, content-type, cache-control'));
	}

}
