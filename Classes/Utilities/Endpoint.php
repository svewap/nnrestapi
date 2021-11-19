<?php 

namespace Nng\Nnrestapi\Utilities;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Composer\Autoload\ClassMapGenerator;

/**
 * Helper für häufig genutzte Api-Funktionen.
 * 
 */
class Endpoint extends \Nng\Nnhelpers\Singleton {

	/**
	 * Methodennamen, die mit diesem Prefix beginnen (und mit `Action` enden) 
	 * werden automatisch als Endpoints berücksichtigt. Beispiel:
	 * `getSomethingAction()` oder `deleteSomethingAction()`
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
	 * Liste aller registrierten Endpoints
	 * @var array
	 */
	private $endpoints = [];
	
	/**
	 * Cache der Endpoints zu Klassen Maps
	 * @var array
	 */
	private $classMapCache = [];
	

	/**
	 * Initialisieren.
	 * Lädt die `nnrestapi` Konfiguration aus der YAML.
	 * 
	 * @return void
	 */
	public function initialize( $request = null ) {

		// \nn\t3::debug( \nn\rest::Settings()->getConfiguration() );

		// if ($this->siteIdentifier) return;

		// $request = $request ?: $GLOBALS['TYPO3_REQUEST'];
		// if (!$request) return;

		// $siteIdentifier = '';
		// $apiConfiguration = [];

		// $site = $request->getAttribute('site');
		// $siteIdentifier = $site->getIdentifier();
		// if (!is_a($site, \TYPO3\CMS\Core\Site\Entity\NullSite::class)) {
		// 	$apiConfiguration = $site->getConfiguration()['nnrestapi'] ?? [];
		// }

		// $this->siteIdentifier = $siteIdentifier;
		// $this->apiConfiguration = $apiConfiguration;
	}

	/**
	 * Einen neuen Endpoint registrieren.
	 * 
	 * Kommt in die `ext_localconf.php` der Extension, die ein Api bereitstellen soll.
	 * Dazu muss die Extension in `ext_emconf.php` eine Abhängigkeit zu `nnrestapi` haben.
	 *  
	 * __Parameter:__
	 * 
	 * `priority` 	Sollten mehrere Endpoints den gleichen Controllername besitzen,
	 * 				wird der Endpoint mit höchster Prio angesteuert
	 * 
	 * `slug`		Optional: Bei Konflikten zwischen mehreren Controllern mit gleichen
	 * 				Namen bestimmt der Slug, welcher Controller angesteuert wird.
	 * 				In dem Beispiel unten wäre der Controller `TestController` über 
	 * 				`api/test` erreichbar. Falls eine andere Extension einen `TestController`
	 * 				hat und dieser mit höherer Priorität registriert wurden, kann der
	 * 				TestController alternativ über `api/example/test` erreicht werden.
	 * 
	 * `namespace`	Der Basis-Pfad (Ordner) zu allen Klassen, die als Controller erreichbar
	 * 				sein sollen. Um den Endpoint `api/test` zu erreichen, müsste es im
	 * 				Beispiel unten eine Klasse mit dem Namespace `Nng\Nnrestapi\Api\Test` 
	 * 				geben.
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

		$priority = $options['priority'] ?? max(array_keys($this->endpoints));
		$this->endpoints[$priority] = $options;
		krsort( $this->endpoints );

		return $this;
	}

	/**
	 * Alle registrierten Endpoints holen
	 * ```
	 * \nn\rest::Endpoint()->getAll()
	 * ```
	 * @return array
	 */
	public function getAll() {
		return $this->endpoints;
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

		// `/en`
		$languagePath = $request->getAttribute('language', null)->getBase()->getPath();

		// `/api/test/123` oder `/en/api/test/123`
		$uri = $request->getUri()->getPath();

		// `/en/api/test/123` ==> `/api/test/123`
		if ($languagePath != '/' && strpos($uri, $languagePath) === 0) {
			$uri = substr( $uri, strlen($languagePath) );
		};

		// Prefix in URL nicht vorhanden? Dann Abbruch.
		if (strpos($uri, $apiPrefix) !== 0) return null;

		// `/api/test/something/1/2/3/4` => ['controller'=>'test', 'action'=>'something', 'uid'=>1, 'param1'=>'2', ...]
		$numParamsToParse = count($this->uriToParameterMapping);
		
		$parts = array_slice(explode('/', substr($uri, strlen($apiPrefix))), 0, $numParamsToParse);
		$paramKeys = $this->uriToParameterMapping;
		$paramValues = array_pad( $parts, $numParamsToParse, '' );
		
		// Slugs, die über `\nn\rest::Endpoint()->register()` registriert wurden, z.B. ['nnrestdemo', 'nnrestapi']
		$endpointSlugs = array_column( $this->getAll(), 'slug' );
		
		// War der URL-Pfad `api/{slug}/...` statt `api/{controller}/...`?
		if (in_array($paramValues[0] ?? '', $endpointSlugs)) {
			
			// dann schieben wir noch ein `ext` vor die keys. Und einen leeren Wert in die Values
			array_unshift( $paramKeys, 'ext' );
			array_push( $paramValues, '' );
		}
		
		$params = array_combine( $paramKeys, $paramValues );
		
		// Ist der `controller` oder `action` ein intval? Dann Ergebnis verschieben.
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

		// Passenden Endpoint finden. `GET test/something` -> \Nng\Nnrestapi\Api\Test->getSomethingAction()`
		$endpoint = $this->find( $reqType, $params['controller'], $params['action'], $params['ext'] );
		$endpoint['args'] = $params;

		if (!$endpoint) {
			$endpoint = $this->findForRoute( $reqType, $uri );
		}

		if (!$endpoint) {
			$endpoint = ['args'=>$params];
		}

		return $endpoint;
	}

	/**
	 * Endpoint mit Klassenname und Methode für ReqType, Controller und Methode finden
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
	 * Endpoint finden für eine bestimmte Route (URI).
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
	 * Herzstück des Routings. 
	 * 
	 * Mapping aller Klassen und Methoden zu den Endpoints holen.
	 * Das Ergebnis wird vollständig gecached, um Performance-Probleme zu vermeiden.
	 * ```
	 * \nn\rest::Endpoint()->getClassMap();
	 * ```
	 * @return array
	 */
	public function getClassMap() {

		$this->initialize();
		$cacheIdentifier = 'nnrestapi_endpoints_' . \nn\rest::Settings()->getSiteIdentifier();

		if ($cache = $this->classMapCache) return $cache;

		//if (!\nn\t3::BackendUser()->isLoggedIn() && $cache = \nn\t3::Cache()->read( $cacheIdentifier )) {
		if ($cache = \nn\t3::Cache()->read( $cacheIdentifier )) {
			return $cache;
		}

		$endpoints = $this->getAll();
		$classesToParse = $this->getClassesToParse();

		$routingMap = [];

		foreach ($classesToParse as $path=>$className) {

			// Zu welchem registrierten Endpoint gehört die Klasse?
			$endpoint = array_shift(array_filter($endpoints, function( $endpoint ) use ( $className) {
				return strpos( $className, $endpoint['namespace'] ) !== false;
			}));
			
			if (!$endpoint) continue;

			$slug = $endpoint['slug'] ?? $endpoint['namespace'];

			$classReflection = new \ReflectionClass( $className );
			$methods = $classReflection->getMethods();
			$classShortName = lcfirst($classReflection->getShortName());

			// Der RegEx, der für das automatische parsen der Methodennamen verwendet wird, z.B. `getSomethingAction`
			$methodNameRegex = '/(' . join('|', self::SUPPORTED_METHOD_PREFIXES) . ')(.*)(Action)/i';

			foreach ($methods as $method) {

				$methodReflection = new \ReflectionMethod( $className, $method->name );

				// Alle Annotations parsen
				$annotationReader = new \Doctrine\Common\Annotations\AnnotationReader();
				$annotations = $annotationReader->getMethodAnnotations( $method ) ?: [];

				
				$reqTypes = [];
				$action = '';
				$arguments = [];

				$endpointData = [
					'slug'			=> $slug,
					'method'		=> $method->name,
					'class' 		=> $className,
					'controller' 	=> $classShortName,
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
					$methodArgs[$variableName] = $argument['simple'] ? [] : [
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
	 * Alle Klassen holen, die registriert wurden und nach Endpoints durchsucht werden müssen.
	 * Key ist der absolute Pfad zur Klasse, Value der Klassenname inkl. Namespace.
	 * ```
	 * \nn\rest::Endpoint()->getClassesToParse();
	 * ```
	 * @return array
	 */
	public function getClassesToParse() {
		$this->initialize();

		$endpoints = $this->getAll();
		$namespaces = array_column( $endpoints, 'namespace' );

		if (\nn\t3::t3Version() >= 11) {
			$composerClassLoader = \TYPO3\CMS\Core\Core\ClassLoadingInformation::getClassLoader();
			$psr4prefixes = $composerClassLoader->getPrefixesPsr4();	
		} else {
			$psr4path = \TYPO3\CMS\Core\Core\Environment::getLegacyConfigPath() . '/' .
						\TYPO3\CMS\Core\Core\ClassLoadingInformation::AUTOLOAD_INFO_DIR .
						\TYPO3\CMS\Core\Core\ClassLoadingInformation::AUTOLOAD_PSR4_FILENAME;
			$psr4prefixes = require( $psr4path );
		}

		// Pfade zu den Klassen ermitteln, deren Namespace über `register` registriert wurden
		$pathsToParse = [];
		foreach ($psr4prefixes as $name=>$paths) {
			$found = array_filter($namespaces, function ($classPrefix) use ($name) {
				return strpos($classPrefix, $name) !== false;
			});
			if ($found) {
				$pathsToParse = array_merge($pathsToParse, $paths);
			}
		}

		// Pfade zu den PHP-Dateien ermitteln, die Api-Endpoints haben
		$classesToParse = [];
		foreach ($pathsToParse as $path) {
			$mappedClasses = array_flip(ClassMapGenerator::createMap( $path ));
			
			$found = array_filter($mappedClasses, function ($className) use ($namespaces) {
				foreach ($namespaces as $name) {
					if (strpos($className, $name) !== false) return true;
				}
				return false;
			});
			$classesToParse = array_merge( $classesToParse, $found );
		}

		return $classesToParse;
	}
	
}