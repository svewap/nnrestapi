<?php
namespace Nng\Nnrestapi\Controller;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \Nng\Nnrestapi\Mvc\Response;
use TYPO3\ClassAliasLoader\ClassAliasMap;

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
//		\nn\rest::Header()->sendControls()->sendContentType();

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

		$request = new \Nng\Nnrestapi\Mvc\Request( $this->request );

		$reqType = $this->checkRequestType();
		$reqVars = $request->getArguments();
		
		$controllerName = $reqVars['controller'] ?? '';
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

		// Passenden Endpoint finden. `GET test/something` -> \Nng\Nnrestapi\Api\Test->getSomethingAction()`
		$endpoint = \nn\rest::Endpoint()->find( $reqType, $controllerName, $actionName, $extSlug );

		if (!$endpoint) {
			if ($route = \nn\rest::Endpoint()->findForRoute( $reqType, $request->getPath() )) {
				$endpoint = $route['endpoint'];
				$request->setArguments($route['arguments']);
			}
		}

		// Kein Endpoint gefunden? Mit 404 abbrechen.
		if (!$endpoint) {
			$checked = array_column( \nn\rest::Endpoint()->getAll(), 'namespace' );
			$classInfo = ucfirst($controllerName) . '->' . $reqType . ucfirst( $actionName ) . 'Action';
			return $this->response->error(404, "Endpoint controller {$classInfo}() not found. Checked these namespaces: " . join( ', ', $checked ) );
		}

		// $request mit Konfigurationen anreichern
		$request->setSettings( \nn\t3::Settings()->get('tx_nnrestapi') );
		$request->setEndpoint( $endpoint );
		$request->setFeUser( \nn\t3::FrontendUser()->getCurrentUser() );

		// `@api\route` und `@api\access` Annotation beim Instanziieren der Klasse ignorieren, sonst Exception!
		$ignore = ['route', 'access', 'example', 'distiller', 'upload'];
		$annotationNamespace = \Nng\Nnrestapi\Utilities\Endpoint::ANNOTATION_NAMESPACE;

		foreach ($ignore as $v) {
			\Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName("{$annotationNamespace}{$v}");
		}

		// Instanz des Endpoints erstellen, z.B. `Nng\Nnrestapi\Api\Test`
		$classInstance = \nn\t3::injectClass( $endpoint['class'] );

		$classInstance->setRequest( $request );
		$classInstance->setResponse( $this->response );

		// Nur wenn Annotation `@access public` exisitert wird action von Nicht-Fe-User erlaubt
		$access = \nn\t3::Arrays($annotations['@access'] ?? '')->trimExplode();
		
		// Prüft, ob aktueller User Zugriff auf Methode hat
		if (!$classInstance->checkAccess( $endpoint )) {
			return $this->response->unauthorized("{$endpoint['class']}->{$endpoint['method']}() has no public access. Please authenticate to access this endpoint or use `@access public` annotation to mark the endpoint as public accessible." );
		}

		// Prüft, ob Dateiuploads existieren. Ersetzt `UPLOAD://file-x` mit Pfad zu Upload-Dateien
		\nn\rest::File()->processFileUploadsInRequest( $request );
		
		// Argumente für Methodenaufruf konstruieren
		if ($arguments = $endpoint['args']) {

			// Methode möchte ein Argument erhalten `->getSomethingAction( $data )` 
			$model = $request->getBody();

			// Methode hat eine DI als erstes Argument definiert `->getSomethingAction( \My\Extname\Model $model )`
			if ($modelName = $arguments[0]['class'] ?? false) {
				$model = \nn\t3::Convert( $model )->toModel( $modelName );
			}
			$result = $classInstance->{$endpoint['method']}( $model ) ?: [];

		} else {

			// Keine Argumente gefordert `->getSomethingAction()` 
			$result = $classInstance->{$endpoint['method']}() ?: [];
		}
		
		// Distiller definiert?
		if ($distiller = $endpoint['distiller'] ?? false) {
			if ($distillerInstance = \nn\t3::injectClass( $distiller )) {
				$distillerInstance->processData( $result );
			}
		}

		$this->response->success( $result );
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
			case 'PATCH':
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
