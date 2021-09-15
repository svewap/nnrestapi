<?php
namespace Nng\Nnrestapi\Controller;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \Nng\Nnrestapi\Mvc\Response;

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
     * @var \Nng\Nnrestapi\Mvc\Response
     */
    protected $response;
	
    /**
     * @var string
     */
	protected $defaultViewObjectName = \Nng\Nnrestapi\Mvc\View\JsonView::class;


	/**
	 * Constructor und Dependeny-Injection
	 * 
	 * @return void
	 */
	public function __construct( Response $response ) {
		$this->response = $response;
	}

	/**
	 * Initialisierung des Requests.
	 * 
	 * Prüft, ob JWT-Token übergeben wurde und authentifiziert den FE-User falls möglich.
	 * 
	 * @return void
	 */
	public function initializeView( \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view ) {

		// `access-control` und `content-type` header senden
		\nn\rest::Header()->sendControls()->sendContentType();

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
	 */
	public function indexAction() {

		print_r( \nn\rest::Endpoint()->getClassMap() );
die();

		$request = new \Nng\Nnrestapi\Mvc\Request( $this->request );

		$reqType = $this->checkRequestType();
		$reqVars = $request->getArguments();
		$payload = $request->getBody();
		
		$controllerName = ucfirst($reqVars['controller'] ?? '');
		$actionName = $reqVars['action'] ?: false;
		$uid = $reqVars['uid'] ?: false;
		$extSlug = $reqVars['ext'] ?: false;
		
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

		// Alle Endpoints, die in `ext_localconf.php` registriert wurden
		$endpoints = \nn\rest::Endpoint()->getAll();
		$endpointsBySlug = \nn\t3::Arrays( $endpoints )->key('slug')->removeEmpty();

		$controllerClassName = false;
		if ($extSlug && $conf = $endpointsBySlug[$extSlug]) {
			$controllerClassName = rtrim($conf['namespace'], '\\') . '\\' . $controllerName;
			if (!method_exists($controllerClassName, $methodName)) {
				$controllerClassName = false;
			}
		}

		// Über `\nn\rest::Endpoint()->register()` bekannten Controllername mit höchster Prio finden
		if (!$controllerClassName) {
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
		if (!$controllerClassName) {
			$checked = array_map( 
				function ( $str ) use ($controllerName, $methodName) {
					return "\\{$str}\\{$controllerName}->{$methodName}()";
				},
				array_column( $endpoints, 'namespace' )
			);
			return $this->error(404, "Endpoint controller {$controllerName}->{$methodName}() not found. Checked these namespaces: " . join( ', ', $checked ) );
		}

		// Klasse instanziieren
		$classInstance = \nn\t3::injectClass( $controllerClassName );
		$classInstance->setRequest( $request );

		// Rechte prüfen.
		$ref = new \ReflectionMethod( $controllerClassName, $methodName );

		// Wird ein bestimmtes Object erwartet?
		$modelName = null;
		$model = $request->getBody();
		if ($firstParam = array_shift($ref->getParameters())) {
			if ($expectedClass = $firstParam->getClass()) {
				$modelName = $expectedClass->getName();
			}
		}

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

		// Model erzeugen?
		if ($modelName) {
			$model = \nn\t3::Convert( $model )->toModel( $modelName );
		}

		$result = $classInstance->{$methodName}( $model ) ?: [];
		
		$this->response->success( $result );
		//$this->view->assign('data', $result);
	}


	/**
	 * Prüft den requestType (GET, POST, ...)
	 * Gibt den requestType in lowerCase zurück.
	 * 
	 * Bricht ab, falls requestType unbekannt ist – oder `OPTIONS` verlangt wird.
	 * 
	 * @return string
	 */
	public function checkRequestType() {

		$httpMethod = $this->request->getMethod();

		switch ($httpMethod) {
			case 'HEAD':
			case 'GET':
			case 'POST':
			case 'PUT':
			case 'DELETE':
				return strtolower($httpMethod);
			case 'OPTIONS':
				return $this->response->noContent();
			default:
				return $this->response->success();
		}
	}


	/**
	 * Parsed die Annotations zu einer Klasse
	 * 
	 * @return array
	 */
	public function parseEndpointAnnotations( $className = '', $methodName = '' ) {

		$ref = new \ReflectionMethod( $className, $methodName );
		// .....
	}
}
