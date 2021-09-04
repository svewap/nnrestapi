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
		\Nng\Nnrestapi\Service\AutoloadService::loadLibraries();

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
	 *	Konfiguration für den RouteEnhancer:
	 *
	 *	```
	 *	routeEnhancers:
	 *		NnrestapiApi:
	 *			type: NncalenderEnhancer
	 *			routePath: '/api/{controller}/{action}/{uid}/{param1}/{param2}'
	 *			defaults:
	 *			controller: 'index'
	 *			action: 'index'
	 *			uid: ''
	 *			param1: ''
	 *			param2: ''
	 *			_arguments:        
	 *				controller: 'controller'
	 *				action: 'action'
	 *				uid: 'uid'
	 *				param1: 'param1'
	 *				param2: 'param2'
	 *	```
	 *
	 */
	public function indexAction() {

		$httpMethod = $this->request->getMethod();
		$reqVars = \nn\t3::Request()->GP() ?? [];
		$payload = json_decode(file_get_contents('php://input'), true) ?: [];
		if (is_array($payload)) {
			$reqVars = \nn\t3::Arrays( $reqVars )->merge( $payload );
		}

		$controllerName = $reqVars['controller'] ?? '';
		$actionName = $reqVars['action'] ?: false;
		$uid = $reqVars['uid'] ?: false;
		
		$reqType = '';

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
			if (!$uid) {
				$reqType = '';
				$actionName = 'index';
			} else {
				$actionName = 'entry';
			}
		}

		if ($uid) $reqVars['uid'] = $uid;

		$methodName = lcfirst($reqType . ucfirst( $actionName ) . 'Action');

		$controllerClassName = 'Nng\Nnrestapi\Api\\' . ucfirst( $controllerName );
		$classInstance = \nn\t3::injectClass( $controllerClassName );

		if (!$classInstance) {
			return $this->error(404, "Endpoint controller {$controllerName} not found." );
		}

		if (!method_exists($classInstance, $methodName)) {
			return $this->error(404, "Method \\{$controllerClassName}->{$methodName}() not found." );
		}
		
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
			return $this->error(403, "{$controllerName}->{$methodName}() no public access." );
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
		$this->throwStatus($statusCode, null, json_encode($data));
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
