<?php
declare(strict_types = 1);

namespace Nng\Nnrestapi\Controller;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \Nng\Nnrestapi\Mvc\Response;
use TYPO3\ClassAliasLoader\ClassAliasMap;
use TYPO3\CMS\Core\Http\PropagateResponseException;

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

		$t3Request = \nn\t3::t3Version() < 11 ? $GLOBALS['TYPO3_REQUEST'] : $this->request; 
		$t3Response = \nn\t3::t3Version() < 11 ? $this->response : $this->responseFactory->createResponse();

		$request = new \Nng\Nnrestapi\Mvc\Request( $t3Request );
		$response = new \Nng\Nnrestapi\Mvc\Response( $t3Response );

		$reqType = $this->checkRequestType( $request, $response );
		$reqVars = $request->getArguments();
		
		$controllerName = $reqVars['controller'] ?? '';
		$actionName = $reqVars['action'] ?: 'index';
		$uid = $reqVars['uid'] ?: false;
		$extSlug = $reqVars['ext'] ?: false;

		// Passenden Endpoint finden. `GET test/something` -> \Nng\Nnrestapi\Api\Test->getSomethingAction()`
		$endpoint = \nn\rest::Endpoint()->find( $reqType, $controllerName, $actionName, $extSlug );

		if (!$endpoint) {
			if ($endpoint = \nn\rest::Endpoint()->findForRoute( $reqType, $request->getPath() )) {
				$request->setArguments($endpoint['route']['args'] ?? []);
			}
		}

		// Kein Endpoint gefunden? Mit 404 abbrechen.
		if (!$endpoint) {
			$checked = array_column( \nn\rest::Endpoint()->getAll(), 'namespace' );
			$classInfo = ucfirst($controllerName) . '->' . $reqType . ucfirst( $actionName ) . 'Action';
			return $response->error(404, "Endpoint controller {$classInfo}() not found. Checked these namespaces: " . join( ', ', $checked ) );
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
		$classInstance->setResponse( $response );
		
		// Prüft, ob aktueller User Zugriff auf Methode hat
		if (!$classInstance->checkAccess( $endpoint )) {

			// Kein Zugriff - oder kein `@api\access public`
			$result = $response->unauthorized("{$endpoint['class']}->{$endpoint['method']}() has no public access. Please authenticate to access this endpoint or use `@access public` annotation to mark the endpoint as public accessible." );
		
		} else {

			// Prüft, ob Dateiuploads existieren. Ersetzt `UPLOAD://file-x` mit Pfad zu Upload-Dateien
			\nn\rest::File()->processFileUploadsInRequest( $request );
			
			// Argumente für Methodenaufruf konstruieren
			if ($arguments = $endpoint['args']) {

				// Methode möchte ein Argument erhalten `->getSomethingAction( $data )` 
				$model = $request->getBody();

				// Kein JSON übergeben, aber uid als GET-Parameter
				if (!$model && $uid = $request->getArguments()['uid'] ?? false) {
					$model = ['uid'=>$uid];
				}

				// Methode hat eine DI als erstes Argument definiert `->getSomethingAction( \My\Extname\Model $model )`
				if ($modelName = $arguments[0]['class'] ?? false) {

					if ($uid = $model['uid'] ?: $request->getArguments()['uid'] ?? false) {
						
						// uid des Models übergeben. Bestehendes Model aus Repo holen
						$repository = \nn\t3::Db()->getRepositoryForModel( $modelName );
						\nn\t3::Db()->ignoreEnableFields( $repository );
						
						if ($existingModel = $repository->findByUid( $uid )) {
							$model = \nn\t3::Obj( $existingModel )->merge( $model );
						} else {
							$model = null;
						}

					} else {

						// Keine uid übergeben. Neues Model erzeugen
						$model = \nn\t3::Convert( $model )->toModel( $modelName );
					}

				}

				$result = $classInstance->{$endpoint['method']}( $model ) ?: [];

			} else {

				// Keine Argumente gefordert `->getSomethingAction()` 
				$result = $classInstance->{$endpoint['method']}() ?: [];
			}
			
			if ($response->getStatus() == 200) {
				// Distiller definiert?
				if ($distiller = $endpoint['distiller'] ?? false) {
					if ($distillerInstance = \nn\t3::injectClass( $distiller )) {
						$distillerInstance->processData( $result );
					}
				}
			}
		}

		$response->setBody( $result );
		throw new PropagateResponseException($response->render(), 1476045871);
	}


	/**
	 * Prüft den requestType (GET, POST, ...)
	 * Gibt den requestType in lowerCase zurück.
	 * 
	 * Bricht ab, falls requestType unbekannt ist – oder `OPTIONS` verlangt wird.
	 * 
	 * @return string
	 */
	public function checkRequestType( $request, $response ) {

		$httpMethod = $request->getMethod();

		switch ($httpMethod) {
			case 'head':
			case 'get':
			case 'post':
			case 'put':
			case 'patch':
			case 'delete':
				return $httpMethod;
			case 'options':
				return $response->noContent();
			default:
				return $response->success();
		}
	}

}
