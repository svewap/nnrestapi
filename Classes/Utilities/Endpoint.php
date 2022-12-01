<?php 

namespace Nng\Nnrestapi\Utilities;

use Composer\Autoload\ClassMapGenerator;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\ClassLoadingInformation;

/**
 * Utility for registering and evaluation Endpoints
 * 
 */
class Endpoint extends \Nng\Nnhelpers\Singleton {

	/**
	 * Method names starting with this prefix (and ending with `Action`) 
	 * are automatically included as endpoints. Example:
	 * `getSomethingAction()` or `deleteSomethingAction()`.
	 * 
	 * @var array
	 */
	const SUPPORTED_METHOD_PREFIXES = ['get', 'post', 'put', 'patch', 'delete'];
	
	/**
	 * Reihenfolge Keys für das Mapping der Parameter in der URL
	 * 
	 * @var array
	 */
	private $uriToParameterMapping = ['controller', 'action', 'uid', 'param1', 'param2', 'param3'];

	/**
	 * List of all endpoints that were registered via `localconf.php`
	 * @var array
	 */
	private $endpoints = [];
	
	/**
	 * List of all extensions, which endpoints should be ignored
	 * @var array
	 */
	private $ignoreEndpoints = [];

	/**
	 * List of all endpoints registered via `localconf.php` and using the `@Api\Endpoint` annotation
	 * @var array
	 */
	private $mergedEndpoints = [];
	
	/**
	 * Cache der Endpoints zu Klassen Maps
	 * @var array
	 */
	private $classMapCache = [];
	

	/**
	 * Initialize
	 * 
	 * @return void
	 */
	public function initialize( $request = null ) {
	}

	/**
	 * Register a new endpoint.
	 * 
	 * Placed in the `ext_localconf.php` of the extension that should provide an api.
	 * Important: The extension must have a dependency on `nnrestapi` in `ext_emconf.php`.
	 *  
	 * __parameters:__
	 * 
	 * `priority` 	If several endpoints have the same controller name,
	 * 				the endpoint with the highest priority will be used.
	 * 
	 * `slug` 		Optional: In case of conflicts between multiple controllers with the same
	 * 				names, the slug determines which controller is called.
	 * 
	 * 				In the example below, the controller `TestController` would be accessible via 
	 * 				`api/test`. If another extension has a `TestController`
	 * 				and it has been registered with a higher priority, then the
	 * 				TestController can alternatively be reached via `api/example/test`.
	 * 
	 * `namespace` 	The base path (folder) to all classes that should be reachable as a controller.
	 * 				To reach the endpoint `api/test`, there would have to be a class with the namespace 
	 * 				in the example below, there would have to be a class with the namespace 
	 * 				`Nng\Nnrestapi\Api\Test`.
	 * 
	 * ```
	 * \nn\rest::Endpoint()->register([
	 * 	'priority' 	=> '70',
	 * 	'slug' 		=> 'example',
	 * 	'namespace'	=> 'Nng\Nnrestapi\Api'
	 * ]);
	 * ```
	 * @return self
	 */
	public function register( $options = [] ) {

		$disableDefaultEndpoints = \nn\rest::Settings()->getExtConf('disableDefaultEndpoints');
		if ($disableDefaultEndpoints && $options['slug'] == 'nnrestapi') {
			return $this;
		}

		// No priority passed? then set to highest priority
		$priority = $options['priority'] ?? max(array_keys($this->endpoints));

		// other extension already uses same priority? then find next free slot
		while ($this->endpoints[$priority] ?? false) {
			$priority++;
		}
		
		$this->endpoints[$priority] = $options;
		krsort( $this->endpoints );

		return $this;
	}

	/**
	 * Get all registered Endpoints.
	 * 
	 * This is the list of raw, registered endpoints which were...
	 * - Registered via `\nn\rest::Endpoint()->register()` in the `localconf.php`
	 * - Registered by using the `@Api\Endpoint()` annotation
	 * 
	 * You should ALWAYS use this method instead of accessing `$this->endpoints` as
	 * the result is cached and it contains the merge data of the above endpoints.
	 * 
	 * ```
	 * \nn\rest::Endpoint()->getAll()
	 * ```
	 * @return array
	 */
	public function getAll() 
	{
		$cacheIdentifier = 'nnrestapi_endpointreg' . \nn\rest::Settings()->getSiteIdentifier();

		if ($cache = $this->mergedEndpoints ?: \nn\t3::Cache()->read( $cacheIdentifier )) {
			return $cache;
		}

		// parse Classes for `@Api\Endpoint()` Annotation
		$this->registerEndpointsWithAnnotation();

		$mergedEndpoints = $this->endpoints;
		$this->mergedEndpoints = $mergedEndpoints;

		return \nn\t3::Cache()->write( $cacheIdentifier, $mergedEndpoints );
	}
	
	/**
	 * Endpoint für übergebenen Request finden.
	 * ```
	 * \nn\rest::Endpoint()->findForRequest('/api/test/2');
	 * ```
	 * @param string $uri
	 * @return array
	 */
	public function findForRequest( $request = null ) {

		$this->initialize( $request );

		// `GET`, `POST`, ...
		$reqType = strtolower($request->getMethod());

		// `/api/`
		$apiPrefix = \nn\rest::Settings()->getApiUrlPrefix();

		// `/en` or `/en/` --> normalize to `/en`
		$languagePath = $request->getAttribute('language', null)->getBase()->getPath();

		// `/api/test/123` oder `/en/api/test/123`
		$uri = $request->getUri()->getPath();

		// `/en/api/test/123` ==> `/api/test/123`
		if ($languagePath != '/' && strpos($uri, $languagePath) === 0) {
			$languagePath = rtrim($languagePath, '/');
			$uri = substr( $uri, strlen($languagePath) );
		};

		// `api/`-prefix not in URL? Then abort.
		if (strpos($uri, $apiPrefix) !== 0) return null;

		// `/api/test/something/1/2/3/4` => ['controller'=>'test', 'action'=>'something', 'uid'=>1, 'param1'=>'2', ...]
		$numParamsToParse = count($this->uriToParameterMapping);
		
		$parts = array_slice(explode('/', substr($uri, strlen($apiPrefix))), 0, $numParamsToParse);
		$paramKeys = $this->uriToParameterMapping;
		$paramValues = array_pad( $parts, $numParamsToParse, '' );
		
		// Slugs, that were registered with `\nn\rest::Endpoint()->register()`, e.g. ['nnrestdemo', 'nnrestapi']
		$endpointSlugs = array_column( $this->getAll(), 'slug' );
		
		// Was the URL-path `api/{slug}/...` instead of `api/{controller}/...`?
		if (in_array($paramValues[0] ?? '', $endpointSlugs)) {
			
			// ... then we will add an `ext` as first key and an empty value
			array_unshift( $paramKeys, 'ext' );
			array_push( $paramValues, '' );
		}
		
		$params = array_combine( $paramKeys, $paramValues );
		
		// Is the `controller` oder `action` an `intval`? Then use it as `uid`.
		$search = ['controller', 'action'];
		foreach ($search as $key) {
			if (is_numeric($params[$key]) && intval($params[$key]) == $params[$key]) {
				$keys = array_keys( $params );
				$values = array_values( $params );
				$index = array_search( $key, $keys );
				array_splice( $values, $index, 0, '');
				array_pop( $values );
				$params = array_combine( $keys, $values );
				$params[$key] = 'index';
			}	
		}

		if (!($params['controller'] ?? false)) {
			$params['controller'] = 'index';
		}
		if (!($params['action'] ?? false)) {
			$params['action'] = 'index';
		}
		if (!($params['ext'] ?? false)) {
			$params['ext'] = '';
		}

		// First: See if a custom routing exists, defined by `@Api\Route` annotation 
		$endpoint = $this->findForRoute( $reqType, $uri );

		// If not, use default method: `GET test/something` -> \Nng\Nnrestapi\Api\Test->getSomethingAction()`
		if (!$endpoint) {
			$endpoint = $this->find( $reqType, $params['controller'], $params['action'], $params['ext'] );
		}

		$endpoint['args'] = $params;

		if (!$endpoint) {
			$endpoint = ['args'=>$params];
		}

		return $endpoint;
	}

	/**
	 * Find all Endpoints related to a certain className.
	 * This method is mainly for debugging purposes – and also for outputting a better
	 * error message in the `MiddleWare/PageResolver` if endpoint could not be found.
	 * 
	 * ```
	 * \nn\rest::Endpoint()->findEndpointsForController( 'example' );
	 * ```
	 * @return array
	 */
	public function findEndpointsForController( $controllerName = '' ) {
		$classMap = $this->getClassMap();
		$endpoints = [];
		foreach ($classMap as $reqType=>$paths) {
			foreach ($paths as $path=>$list) {
				foreach ($list as $extname=>$config) {
					if ($controllerName != $config['controller'] ?? '') {
						continue;
					}
					$endpoints[] = $config;
				}
			}
		}
		return $endpoints;
	}

	/**
	 * Find an Endpoint by Classname and method (ReqType). Return enpoint array with controller / method 
	 * ```
	 * \nn\rest::Endpoint()->find( 'get', 'controller', 'action' );
	 * \nn\rest::Endpoint()->find( true, 'controller', 'action' );
	 * ```
	 * @return array
	 */
	public function find( $reqType = '', $controllerName = '', $methodName = '', $extSlug = '' ) {
		$classMap = $this->getClassMap();

		if ($reqType === true) {
			foreach ($classMap as $type=>$v) {
				if ($result = $this->find($type, $controllerName, $methodName, $extSlug)) {
					return $result;
				}
			}
			return [];
		}

		$config = $classMap[$reqType]["{$controllerName}/{$methodName}"] ?? [];
		if ($extSlug && ($endpoint = $config[$extSlug] ?? false)) return $endpoint;
		return array_shift( $config );
	}

	/**
	 * Find endpoint for a given Route (URI).
	 * ```
	 * \nn\rest::Endpoint()->find( 'get', 'api/test/to/some/{name}/{uid}/somewhere' );
	 * ```
	 * @return array
	 */
	public function findForRoute( $reqType = '', $path = '' ) {
		$classMap = $this->getClassMap();
		$routes = $classMap[$reqType] ?? [];

		foreach ($routes as $route) {
			foreach ($route as $config) {
				if ($match = $config['route']['match'] ?? false) {
					if (preg_match($match, $path, $matches)) {
						$args = array_slice( $matches, 2 );
						$config['route']['arguments'] = array_combine(array_keys($config['route']['arguments']), $args);
						return $config;
					}	
				}
			}
		}
		return [];
	}

	/**
	 * Heart of the routing. 
	 * 
	 * Get mapping of all classes and methods to endpoints.
	 * The result is fully cached to avoid performance problems.
	 * ```
	 * \nn\rest::Endpoint()->getClassMap();
	 * ```
	 * @return array
	 */
	public function getClassMap() {

		$this->initialize();
		$cacheIdentifier = 'nnrestapi_endpoints_' . \nn\rest::Settings()->getSiteIdentifier();

		if ($cache = $this->classMapCache ?: \nn\t3::Cache()->read( $cacheIdentifier )) {
			return $cache;
		}

		$endpoints = $this->getAll();
		$classesToParse = $this->getClassesToParse();
		$routingMap = [];

		foreach ($classesToParse as $path=>$className) {

			// To which registered endpoint does the class belong?
			$endpoint = array_shift(array_filter($endpoints, function( $endpoint ) use ( $className) {
				return strpos( $className, $endpoint['namespace'] ) !== false;
			}));

			if (!$endpoint) continue;

			$slug = $endpoint['slug'] ?? $endpoint['namespace'];

			$classReflection = new \ReflectionClass( $className );
			$methods = $classReflection->getMethods();
			$classShortName = lcfirst($classReflection->getShortName());

			// The regex used for automatic parsing of method names, e.g. `getSomethingAction`
			$methodNameRegex = '/(' . join('|', self::SUPPORTED_METHOD_PREFIXES) . ')(.*)(Action)/i';

			foreach ($methods as $method) {


				// Parse all Annotations
				$annotationReader = new \Doctrine\Common\Annotations\AnnotationReader();
				$annotations = $annotationReader->getMethodAnnotations( $method ) ?: [];

				// Parse the DocComment of the class
				$classAnnotations = $annotationReader->getClassAnnotations( $classReflection ) ?: [];
				$classAnnotationData = [];

				// Call `mergeDataForClassInfo()` in the annotation-class, if exists
				foreach ($classAnnotations as $annotation) {
					if (method_exists($annotation, 'mergeDataForClassInfo')) {
						$annotation->mergeDataForClassInfo( $classAnnotationData );
					}
				}

				// Prefer `@Api\Endpoint("pathname")`, if defined in DocComment of Class
				$customClassName = $classAnnotationData['customClassName'] ?? '';
				$classShortName = $customClassName ?: $classShortName;

				$reqTypes = [];
				$action = '';
				$arguments = [];

				$endpointData = [
					'slug'				=> $slug,
					'method'			=> $method->name,
					'class' 			=> $className,
					'controller' 		=> $classShortName,
				];

				// Call `mergeDataForEndpoint()` in the annotation-class, if exists
				foreach ($annotations as $annotation) {
					if (method_exists($annotation, 'mergeDataForEndpoint')) {
						$annotation->mergeDataForEndpoint( $endpointData );
					}
				}

				if ($route = &$endpointData['route'] ?? false) {

					// Custom Route was defined in annotation
					$reqTypesFromRoute = $route['reqTypes'] ?? [];
					array_push( $reqTypes, ...$reqTypesFromRoute );
					
					$action = $route['path'];
					unset($route['reqTypes']);

				} else if (preg_match($methodNameRegex, $method->name, $matches)) {

					// Standard name for action was used (`getSomethingAction`)

					// `get`, `post`... comes from the method-name prefix
					$reqTypes[] = strtolower( $matches[1] );

					// `IndexSomewhere` => `indexSomewhere`
					$action = lcfirst($matches[2]);

				} else {

					// Not a route - and not a standard name. Ignore!
					continue;
				}


				// Parse method arguments: Which DI-Objects / Models are expected as arguments?
				$arguments = \nn\t3::Obj()->getMethodArguments( $className, $method->name );

				$methodArgs = [];
				foreach ($arguments as $variableName=>$argument) {
					$methodArgs[$variableName] = $argument['simple'] ? [
						'type' 		=> $argument['elementType'],
					] : [
						'type'		=> 'object',
						'element' 	=> $argument['elementType'],
						'storage' 	=> $argument['storageType'],
					];	
				}

				$endpointData = array_merge( $endpointData, [
					'action' 		=> $action,
					'methodArgs'	=> $methodArgs,
				]);

				$path = ltrim("{$classShortName}/{$action}", '/');

				foreach ($reqTypes as $reqType) {
					if (!isset($routingMap[$reqType])) $routingMap[$reqType] = [];
					if (!isset($routingMap[$reqType][$path])) $routingMap[$reqType][$path] = [];
					$routingMap[$reqType][$path][$slug] = $endpointData;
				}
				
			}
			
		}

		$this->classMapCache = $routingMap;
		return \nn\t3::Cache()->write( $cacheIdentifier, $routingMap );
	}


	/**
	 * Get a list of all Classes that have the `@Api\Endpoint` Annotation in the DocComment.
	 * This is the alternative way of registering an endpoint.
	 * 
	 * @return array
	 */
	public function registerEndpointsWithAnnotation() {
		
		$packageManager = \nn\t3::injectClass( \TYPO3\CMS\Core\Package\PackageManager::class );
		$regexPattern = "/@[a-zA-Z]*\\\Endpoint/";
		$registeredClasses = [];

		foreach($packageManager->getActivePackages() as $extkey=>$package ) {

			// Exclude core extensions
			if ($package->isPartOfFactoryDefault()) continue;
			
			// ignore nnhelpers
			if ($extkey == 'nnhelpers') continue;

			// Normalize path to `Classes`
			$key = 'psr-4';
			$valueFromManifest = $package->getValueFromComposerManifest( 'autoload' )->$key ?? [];

			$psr4 = array_map(function ( $item ) {
				return is_array($item) ? $item : [$item];
			}, (array) $valueFromManifest);
			
			$extPath = $package->getPackagePath();
			$filesToParse = [];

			foreach ($psr4 as $classPaths) {
				foreach ($classPaths as $classPath) {
					$files = \nn\rest::File()->getAllInFolder( $extPath . $classPath , true, 'php' );
					foreach ($files as $file) {
						$filesToParse[] = $file;
					}
				}
			}

			// No composer psr-4 / autoload? Fallback...
			if (!$filesToParse) {
				$classesFolder = \nn\t3::Environment()->extPath( $extkey ) . 'Classes/';
				$files = \nn\rest::File()->getAllInFolder( $classesFolder );
				$filesToParse = array_merge( $filesToParse, $files );
			}
			
			foreach ($filesToParse as $file) {
				// preflight: string-compare if `@Api\Endpoint` is somewhere in the script. Not pretty, but `ReflectionClass` throws an uncatchable Exception.
				$className = \Nng\Nnhelpers\Helpers\DocumentationHelper::getClassNameFromFile($file);
				$content = file_get_contents( $file );
				if (!$content || !preg_match($regexPattern, $content)) {
					continue;
				}

				// ignore because this is the Annotation itself. It describes how to use the Annotation
				if ($className == \Nng\Nnrestapi\Annotations\Endpoint::class) {
					continue;					
				}

				// then make sure, the `@Api\Endpoint` string is in the DocComment
				$reflection = new \ReflectionClass($className);
				if (preg_match($regexPattern, $reflection->getDocComment())) {

					$this->register([
						'priority' 	=> 0,
						'slug' 		=> $extkey,
						'namespace'	=> $className,
					]);
					$registeredClasses[$file] = $className;
				}
			}
		}

		return $registeredClasses;
	}

	/**
	 * Get all classes that have been registered and need to be searched for endpoints.
	 * Key is the absolute path to the class, value is the class name incl. namespace.
	 * ```
	 * \nn\rest::Endpoint()->getClassesToParse();
	 * ```
	 * @return array
	 */
	public function getClassesToParse() 
	{
		$this->initialize();

		$endpoints = $this->getAll();
		$namespaces = array_column( $endpoints, 'namespace' );

		$composerClassLoader = ClassLoadingInformation::getClassLoader();
		$psr4prefixes = $composerClassLoader->getPrefixesPsr4();

		// Filter namespaces that were registered with `\nn\rest::Endpoint()->register(...)` 
		$pathsToParse = [];
		foreach ($psr4prefixes as $name=>$paths) {
			$found = array_filter($namespaces, function ($classPrefix) use ($name, $paths) {
				if (strpos($classPrefix, $name) !== false) {
					return true;
				}
			}, ARRAY_FILTER_USE_BOTH);
			if ($found) {
				$pathsToParse = array_merge($pathsToParse, $paths);
			}
		}

		// Determine paths to PHP files that have Api endpoints
		$classesToParse = [];
		foreach ($pathsToParse as $path) {
			$mappedClasses = array_flip(ClassMapGenerator::createMap( $path ));
			
			$found = array_filter($mappedClasses, function ($className, $path) use ($namespaces) {
				foreach ($namespaces as $name) {
					if (strpos($className, $name) !== false) return true;
				}
				return false;
			}, ARRAY_FILTER_USE_BOTH);
			$classesToParse = array_merge( $classesToParse, $found );
		}

		$classesToParse = array_unique($classesToParse);

		return $classesToParse;
	}
	
}