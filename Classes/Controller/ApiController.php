<?php
declare(strict_types = 1);

namespace Nng\Nnrestapi\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;

use \Nng\Nnrestapi\Exception\PropertyValidationException;
use \Nng\Nnrestapi\Error\ApiError;

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

		// check if user is allowed to access hidden records (simulates a backend user)
		$showHiddenFromAnnotation 	= $endpoint['includeHidden'] ?? false;		// via `@Api\IncludeHidden` annotation?
		$showHiddenFromFeUser 		= $request->isAdmin();						// via "admin"-checkbox set in fe-user?

		if ($showHiddenFromAnnotation || $showHiddenFromFeUser) {
			\nn\rest::Settings()->setIgnoreEnableFields( true );
		}
		
		// create an instance of the endpoint `Nng\Nnrestapi\Api\Test`
		$classInstance = \nn\t3::injectClass( $endpoint['class'] );

		// set request and response wrappers in class instance
		$classInstance->setRequest( $request );
		$classInstance->setResponse( $response );

		// checks, if LanguageAspect needs to be set to different language. Will decide, if language-overlay is loaded for records.
		$overlayLanguageUid = $classInstance->determineLanguage( $endpoint );
				
		if ($overlayLanguageUid > 0) {
			$site = \nn\rest::Settings()->getSite();
			$language = $site->getLanguageById( $overlayLanguageUid );

			$languageAspect = LanguageAspectFactory::createFromSiteLanguage($language);
			$context = GeneralUtility::makeInstance(Context::class);
			$context->setAspect('language', $languageAspect);

			// keep the TYPO3_REQUEST in sync with the new language in case other extensions are relying on it
			if ($GLOBALS['TYPO3_REQUEST'] ?? false) {
				$GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST']->withAttribute('language', $language);
			}
		}

		// add public accessible properties for convenience
		$classInstance->languageUid = $overlayLanguageUid;
		$classInstance->feUser = $request->getFeUser();

		// now that all important things are set, we can call `afterInitialization()`
		if (method_exists($classInstance, 'afterInitialization')) {
			$classInstance->afterInitialization();
		}
		
		// check if there are `@Api\Security\*` issues, e.g. if IP was flagged or too many requests were made
		if (!$classInstance->checkSecurity( $endpoint )) {			
			$result = $response->unauthorized("{$endpoint['class']}->{$endpoint['method']}() has blocked the access during the security preflight.", 403001 );
		}

		// Set headers (max-age etc.)
		$classInstance->setDefaultHeaders( $endpoint );

		// check if access is granted to requested class->method
		if (!$classInstance->checkAccess( $endpoint )) {
			
			// no access allowed - or no `@Api\Access("public")` set
			$result = $response->unauthorized("{$endpoint['class']}->{$endpoint['method']}() has no public access or you are not authenticated. Please check your `@Api\Access()` annotation at the method. Your IP was " . $this->request->getRemoteAddr() );
			
		} else {

			// @Api\Cache enabled? Then return result from Cache if possible
			if ($endpoint['cache'] ?? false) {
				$result = \nn\t3::Cache()->get( $cacheIdentifer ) ?? [];
			}
			
			// Nothing returned from cache? Then continue parsing the request 
			if (!$result) {

				// check, if fileuploads were included in multipart/form-data. Replace placeholders `UPLOAD://file-x` with real path after moving files
				\nn\rest::File()->processFileUploadsInRequest( $request );

				// does the method expect argument(s), e.g. `->getSomethingAction( $data )`?
				if ($endpoint['methodArgs']) {
					
					// get array of arguments to apply to method
					try {
						$argumentsToApply = $this->getArgumentsToApply( $endpoint, $request, $response );
						$result = $classInstance->{$endpoint['method']}( ...$argumentsToApply ) ?: [];
					} catch( PropertyValidationException $e ) {
						$result = $response->invalid( $e->getMessage() );
					} catch ( ApiError $e ) {
						$result = $response->error( $e );
					} catch ( \Error $e ) {
						\nn\t3::Error( $e->getMessage(), $e->getCode() );
					}
	
				} else {
	
					// No arguments expected in method (`->getSomethingAction()`)
					try {
						$result = $classInstance->{$endpoint['method']}() ?: [];
					} catch( ApiError $e ) {
						$result = $response->error( $e );
					} catch ( \Error $e ) {
						\nn\t3::Error( $e->getMessage(), $e->getCode() );
					}
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

	/**
	 * Determine the arguments to apply to the endpoints' method.
	 * Creates an array with arguments in the order of the arguments
	 * the method expects.
	 * 
	 * @param array $endpoint
	 * @param \Nng\Nnrestapi\Mvc\Request $request
	 * @param \Nng\Nnrestapi\Mvc\Response $response
	 * @return array
	 * @throws PropertyValidationException
	 */
	public function getArgumentsToApply( $endpoint, $request, $response ) 
	{
		$expectedArguments = $endpoint['methodArgs'];
		
		$modelData = $request->getBody();
		$nothingToMerge = !$modelData;
		$reqVars = $request->getArguments();
		$argumentsToApply = [];

		// no JSON passed, but a `uid` was passed as path in in the GET-request? `api/endpoint/123`
		if (!$modelData && $uid = $reqVars['uid'] ?? false) {
			$modelData = ['uid'=>$uid];
		}

		// prepare a list of arguments to apply to method when it is called (dependency injection)
		foreach ($expectedArguments as $varName=>$varDefinition) {

			$valueToApply = '';

			$modelName = $varDefinition['element'] ?? false;
			$expectedType = $varDefinition['type'] ?? false;

			if ($expectedType == 'object' && $modelName) {
				
				// @todo: parse ObjectStorages and Arrays in future versions
				
				// was a uid passed? Then get existing model from database
				if ($uid = $modelData['uid'] ?? $reqVars[$varName] ?? false) {

					// uid was passed. Retrieve Model (without the need of instanciating the repository)
					$existingModel = \nn\t3::Db()->get( $uid, $modelName );

					if ($existingModel) {
						
						if ($nothingToMerge) {
							$model = $existingModel;
						} else {

							// merge data from request with existing model?
							if ($autoMerge) {
								$model = \nn\t3::Obj( $existingModel )->merge( $modelData );
							} else {
								$model = $existingModel;
							}

							// validate (use `@TYPO3\CMS\Extbase\Annotation\Validate` on properties of the model)
							$errors = \nn\rest::Validator()->validateModel( $model );
							if ($errors) {
								array_walk( $errors, function( &$errors, $field ) {
									$errors = "`{$field}` [" . join(' - ', $errors) . "]";
								});
								throw new PropertyValidationException( "Error validating properties: " . join('; ', $errors) );
							}
						}
						
					} else {
						$model = null;
					}
					
				} else if ($modelData) {
					
					// this happens, if the POST-body was not a JSON!
					if (!is_array($modelData)) {
						\nn\t3::Exception('JSON could not be mapped to an array. Please check if you are passing a valid JSON with {"keys":"values"} and not just a string! Received: ' . $modelData);
					}
					
					// no uid passed. Create a new model.
					// always set tstamp and crdate for new models (if not already set)
					$modelData = array_merge(['tstamp'=>time(), 'mktime'=>time()], $modelData);

					// Default values defined for the new model defined in TypoScript?
					if ($defaultValues = 
							$this->settings['insertDefaultValues'][$modelName] 
						??  $this->settings['insertDefaultValues']['\\' . $modelName]
						??  false) {
						$modelData = array_merge($defaultValues, $modelData);
					}

					$model = \nn\t3::Convert( $modelData )->toModel( $modelName );
				}	
				
				$valueToApply = $model;
				
			} else {

				// Map `/path/{uid}` to `methodName( $uid )`
				$valueToApply = $reqVars[$varName] ?? null;

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

			$modelData = [];
			$model = null;
		}

		return $argumentsToApply;
	}

}
