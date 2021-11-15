<?php
declare(strict_types = 1);

namespace Nng\Nnrestapi\Controller;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \Nng\Nnrestapi\Mvc\Response;
use TYPO3\ClassAliasLoader\ClassAliasMap;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * ApiController
 * 
 */
class ApiController extends AbstractApiController {

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
		
		$request = $this->request;
		$response = $this->response;
		$endpoint = $request->getEndpoint();
		$reqVars = $request->getArguments();

		$uid = $reqVars['uid'] ?: false;

		$result = [];
		$cacheIdentifer = [$endpoint['class'], $endpoint['method']];

		// Instanz des Endpoints erstellen, z.B. `Nng\Nnrestapi\Api\Test`
		$classInstance = \nn\t3::injectClass( $endpoint['class'] );

		$classInstance->setRequest( $request );
		$classInstance->setResponse( $response );

		// Prüfen, ob User Zugriff auf versteckte Datensätze hat, ähnlich einem Backend-Admin
		$showHiddenFromAnnotation 	= $endpoint['includeHidden'] ?? false;		// per `@Api\IncludeHidden` Annotation
		$showHiddenFromFeUser 		= $request->isAdmin();						// per Admin-Checkbox am fe-user

		if ($showHiddenFromAnnotation || $showHiddenFromFeUser) {
			// die nnrestapi-Xclasses übernehmen jetzt die Kontrolle!
			\nn\rest::Settings()->setIgnoreEnableFields( true );
		}
		
		// Prüft, ob aktueller User Zugriff auf Methode hat
		if (!$classInstance->checkAccess( $endpoint )) {
			
			// Kein Zugriff - oder kein `@api\access public`
			$result = $response->unauthorized("{$endpoint['class']}->{$endpoint['method']}() has no public access or you are not authenticated. Please check your `@Api\Access()` annotation at the method." );
			
		} else {
			
			// Prüft, ob Dateiuploads existieren. Ersetzt `UPLOAD://file-x` mit Pfad zu Upload-Dateien
			\nn\rest::File()->processFileUploadsInRequest( $request );

			$requestArguments = $request->getArguments();

			// Argumente für Methodenaufruf konstruieren
			if ($arguments = $endpoint['methodArgs']) {

				// Methode möchte ein Argument erhalten `->getSomethingAction( $data )` 
				$model = $request->getBody();
				$nothingToMerge = !$model;

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
							
							$existingModel = \nn\t3::Db()->get( $uid, $modelName );

							if ($existingModel) {
								
								if ($nothingToMerge) {
									$model = $existingModel;
								} else {
									$model = \nn\t3::Obj( $existingModel )->merge( $model );
								}
								
							} else {
								$model = null;
							}
							
						} else {
							
							// Keine uid übergeben. Neues Model erzeugen
							
							// Default values defined for the new model?
							if ($defaultValues = $this->settings['insertDefaultValues'][$modelName] ?? false) {
								$model = array_merge($defaultValues, $model);
							}
							
							$model = \nn\t3::Convert( $model )->toModel( $modelName );
							
						}	
						
						$valueToApply = $model;
						
					} else {
						
						// Map `/path/{uid}` to `methodName( $uid )`
						$valueToApply = $requestArguments[$varName] ?? null;
						
					}
					
					$argumentsToApply[] = $valueToApply;
				}
				
				// @Api\Cache enabled? Then return result from Cache if possible
				if ($endpoint['cache'] ?? false) {
					$result = \nn\t3::Cache()->get( $cacheIdentifer ) ?? [];
				}

				// No result or nothing in Cache? Then call method.
				if (!$result) {
					$result = $classInstance->{$endpoint['method']}( ...$argumentsToApply ) ?: [];
				}

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
		$json = $response->render();

		// @Api\Cache enabled? Then write result to cache
		if ($endpoint['cache'] ?? false) {
			\nn\t3::Cache()->set( $cacheIdentifer, $json );
		}

		return $json;
	}

}
