<?php
declare(strict_types = 1);

namespace Nng\Nnrestapi\Controller;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \Nng\Nnrestapi\Mvc\Response;
use TYPO3\ClassAliasLoader\ClassAliasMap;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Nnrestapi
 * 
 */
class ApiController {

	/**
	 * @var \Nng\Nnrestapi\Mvc\Request
	 */
	public $request;

	/**
	 * @var \Nng\Nnrestapi\Mvc\Response
	 */
	public $response;

	/**
	 * Basis-Endpoint für alle Requests an die REST-Api
	 * Übernimmt die Delegation an den entsprechenden Controller unter Classes/Api
	 * 
	 * Wird über die Middleware `PageResolver` dieser Extension instanziiert.
	 * 
	 * Prüft, ob der User die Rechte hat, eine Methode aufzurufen.
	 * Wird über die Annotations der Klassen-Methoden gesteuert, z.B.
	 * 
	 * `@access public` -> Zugriff auch für nicht-feUser erlaubt
	 * 
	 * @return
	 */
	public function indexAction() {

		/*
		$frontendUser = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class);
		$frontendUser->start();
		$frontendUser->logoff();
		*/
		/*
		\nn\t3::debug( $sessionId );
		\nn\t3::debug( \nn\t3::FrontendUser()->getGroups( ) );
		\nn\t3::debug( \nn\t3::FrontendUser()->get() );
		\nn\t3::debug( $this->request->getFeUser() );
		\nn\t3::debug( $this->request );
		die();
		//*/
		
		$request = $this->request;
		$response = $this->response;
		$endpoint = $request->getEndpoint();
		$reqVars = $request->getArguments();
		
		$uid = $reqVars['uid'] ?: false;

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
			
			$requestArguments = $request->getArguments();

			// Argumente für Methodenaufruf konstruieren
			if ($arguments = $endpoint['methodArgs']) {

				// Methode möchte ein Argument erhalten `->getSomethingAction( $data )` 
				$model = $request->getBody();

				// Kein JSON übergeben, aber uid als GET-Parameter
				if (!$model && $uid = $request->getArguments()['uid'] ?? false) {
					$model = ['uid'=>$uid];
				}

				$argumentsToApply = [];

				foreach ($arguments as $varName=>$varDefinition) {

					$valueToApply = '';

					// ToDO: ObjectStorage und Array berücksichtigen
					if ($modelName = $varDefinition['element'] ?? false) {

						if ($uid = $model['uid'] ?: $request->getArguments()['uid'] ?? false) {
						
							// uid des Models übergeben. Bestehendes Model aus Repo holen
							$repository = \nn\t3::Db()->getRepositoryForModel( $modelName );
							\nn\t3::Db()->ignoreEnableFields( $repository );
							
							if ($existingModel = $repository->findByUid( $uid )) {


								$model = \nn\t3::Obj( $existingModel )->merge( $model );
\nn\t3::debug( $existingModel->getFiles(), 'OLD');
\nn\t3::debug( $model->getFiles(), 'NEW');
// $result = $existingModel;
// //$existingModel->setFiles( new \TYPO3\CMS\Extbase\Persistence\ObjectStorage() );
// $repository->update( $existingModel );
// \nn\t3::Db()->persistAll();
//\nn\t3::debug($model);die();
							} else {
								$model = null;
							}
	
						} else {
	
							// Keine uid übergeben. Neues Model erzeugen
							$model = \nn\t3::Convert( $model )->toModel( $modelName );

						}	

						$valueToApply = $model;

					} else {

						// Map `/path/{uid}` to `methodName( $uid )`
						$valueToApply = $requestArguments[$varName] ?? null;
						
					}

					$argumentsToApply[] = $valueToApply;
				}
// WIEDER REIN!
//				$result = $classInstance->{$endpoint['method']}( ...$argumentsToApply ) ?: [];
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

		// Result was already rendered (e.g. a 404 or 403 was returned)
		if (is_a($result, \TYPO3\CMS\Core\Http\Response::class)) {
			return $result;
		}

		$response->setBody( $result );
		return $response->render();
	}


	/**
	 * @return  \Nng\Nnrestapi\Mvc\Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * @param   \Nng\Nnrestapi\Mvc\Request  $request  
	 * @return  self
	 */
	public function setRequest($request) {
		$this->request = $request;
		return $this;
	}

	/**
	 * @return  \Nng\Nnrestapi\Mvc\Response
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * @param   \Nng\Nnrestapi\Mvc\Response  $response  
	 * @return  self
	 */
	public function setResponse($response) {
		$this->response = $response;
		return $this;
	}
}
