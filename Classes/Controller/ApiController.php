<?php
declare(strict_types = 1);

namespace Nng\Nnrestapi\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;


/**
 * ApiController
 * 
 */
class ApiController extends AbstractApiController 
{
	/**
	 * Base endpoint for all requests to the REST api.
	 * Takes care of delegation to the appropriate controller located at Classes/Api.
	 * 
	 * Instantiated from the middleware `PageResolver` of this extension.
	 * 
	 * - Checks requested language
	 * - Checks if the user has the rights to call a method.
	 * 
	 * @return \TYPO3\CMS\Core\Http\Response
	 */
	public function indexAction() 
	{
		$request = $this->request;
		$response = $this->response;
		$endpoint = $request->getEndpoint();
		$reqVars = $request->getArguments();

		$uid = $reqVars['uid'] ?: false;

		$result = [];
		$cacheIdentifer = [$endpoint['class'], $endpoint['method']];

		// create an instance of the endpoint `Nng\Nnrestapi\Api\Test`
		$classInstance = \nn\t3::injectClass( $endpoint['class'] );

		// set request and response wrappers in class instance
		$classInstance->setRequest( $request );
		$classInstance->setResponse( $response );

		// check if user is allowed to access hidden records (simulates a backend user)
		$showHiddenFromAnnotation 	= $endpoint['includeHidden'] ?? false;		// via `@Api\IncludeHidden` annotation?
		$showHiddenFromFeUser 		= $request->isAdmin();						// via "admin"-checkbox set in fe-user?

		if ($showHiddenFromAnnotation || $showHiddenFromFeUser) {
			\nn\rest::Settings()->setIgnoreEnableFields( true );
		}

		// checks, if LanguageAspect needs to be set to different language. Will decide, if language-overlay is loaded for records.
		$overlayLanguageUid = $classInstance->determineLanguage( $endpoint );
		if ($overlayLanguageUid > 0) {
			$site = \nn\rest::Settings()->getSite();
			$language = $site->getLanguageById( $overlayLanguageUid );

			$languageAspect = LanguageAspectFactory::createFromSiteLanguage($language);
			$context = GeneralUtility::makeInstance(Context::class);
			$context->setAspect('language', $languageAspect);
		}

		// check if access is granted to requested class->method
		if (!$classInstance->checkAccess( $endpoint )) {
			
			// no access allowed - or no `@Api\Access("public")` set
			$result = $response->unauthorized("{$endpoint['class']}->{$endpoint['method']}() has no public access or you are not authenticated. Please check your `@Api\Access()` annotation at the method. Your IP was " . $this->request->getRemoteAddr() );
			
		} else {
			
			// check, if fileuploads were included in multipart/form-data. Replace placeholders `UPLOAD://file-x` with real path after moving files
			\nn\rest::File()->processFileUploadsInRequest( $request );

			// prepare dependency injection			
			$requestArguments = $request->getArguments();

			if ($arguments = $endpoint['methodArgs']) {

				// method expects argument(s) like `->getSomethingAction( $data )` 
				$model = $request->getBody();
				$nothingToMerge = !$model;

				// no JSON passed, but a `uid` was passed as path in in the GET-request? `api/endpoint/123`
				if (!$model && $uid = $request->getArguments()['uid'] ?? false) {
					$model = ['uid'=>$uid];
				}

				// prepare a list of arguments to apply to method when it is called (dependency injection)
				$argumentsToApply = [];
				foreach ($arguments as $varName=>$varDefinition) {

					$valueToApply = '';

					$modelName = $varDefinition['element'] ?? false;
					$expectedType = $varDefinition['type'] ?? false;

					if ($expectedType == 'object' && $modelName) {
						
						// @todo: parse ObjectStorages and Arrays in future versions

						// was a uid passed? Then get existing model from database
                        if ($uid = $model['uid'] ?? $request->getArguments()['uid'] ?? false) {

							// uid was passed. Retrieve Model (without the need of instanciating the repository)
							$existingModel = \nn\t3::Db()->get( $uid, $modelName );

							if ($existingModel) {
								
								if ($nothingToMerge) {
									$model = $existingModel;
								} else {

									// merge data from request with existing model
									$model = \nn\t3::Obj( $existingModel )->merge( $model );

									// validate (use `@TYPO3\CMS\Extbase\Annotation\Validate` on properties of the model)
									$errors = \nn\rest::Validator()->validateModel( $model );
									if ($errors) {
										array_walk( $errors, function( &$errors, $field ) {
											$errors = "`{$field}` [" . join(' - ', $errors) . "]";
										});
										return $response->invalid("Error validating properties: " . join('; ', $errors) );
									}
								}
								
							} else {
								$model = null;
							}
							
						} else {
							
							// no uid passed. Create a new model.
							// always set tstamp and crdate for new models (if not already set)
							$model = array_merge(['tstamp'=>time(), 'mktime'=>time()], $model);

							// Default values defined for the new model defined in TypoScript?
							if ($defaultValues = 
									$this->settings['insertDefaultValues'][$modelName] 
								??  $this->settings['insertDefaultValues']['\\' . $modelName]
								??  false) {
								$model = array_merge($defaultValues, $model);
							}

							$model = \nn\t3::Convert( $model )->toModel( $modelName );
						}	
						
						$valueToApply = $model;
						
					} else {
						
						// Map `/path/{uid}` to `methodName( $uid )`
						$valueToApply = $requestArguments[$varName] ?? null;

						// @todo: Clean this
						// Integer expected as argument
						switch ($expectedType) {
							case 'integer':
								$valueToApply = intval($valueToApply);
								break;
							case 'string':
								$valueToApply = "{$valueToApply}";
								break;
						}
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

				// No arguments expected in method (`->getSomethingAction()`)
				$result = $classInstance->{$endpoint['method']}() ?: [];

			}
			
			if ($response->getStatus() == 200) {
				// Distiller defined?
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
