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
	 * Nur Annotations berücksichtigen, die mit diesem
	 * Prefix beginnen, z.B. `@api\access`
	 * 
	 * @var string
	 */
	const ANNOTATION_NAMESPACE = 'api\\';

	/**
	 * Methodennamen, die mit diesem Prefix beginnen (und mit `Action` enden) 
	 * werden automatisch als Endpoints berücksichtigt. Beispiel:
	 * `getSomethingAction()` oder `deleteSomethingAction()`
	 * 
	 * @var array
	 */
	private $supportedMethodPrefixes = ['get', 'post', 'put', 'patch', 'delete'];

	/**
	 * Liste aller registrierten Endpoints
	 * @var array
	 */
	private $endpoints = [];
	
	/**
	 * Cache der User (`fe_users`, `be_users`)
	 * @var array
	 */
	private $userCache = [];
	
	/**
	 * Cache der Endpoints zu Klassen Maps
	 * @var array
	 */
	private $classMapCache = [];

	/**
	 * Site identifier (= Name der siteConfig-yaml)
	 * @var string
	 */
	private $siteIdentifier = '';

	/**
	 * Site Configuration (Array der siteConfig-yaml)
	 * @var string
	 */
	private $siteConfiguration = [];


	/**
	 * Initialisieren.
	 * 
	 * @return void
	 */
	public function initialize() {

		if ($this->siteIdentifier) return;

		$siteIdentifier = '';
		$siteConfiguration = [];

		if ($GLOBALS['TYPO3_REQUEST'] && $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site')) {
			$siteIdentifier = $site->getIdentifier();
			if (!is_a($site, \TYPO3\CMS\Core\Site\Entity\NullSite::class)) {
				$siteConfiguration = $site->getConfiguration();
			}
		}

		$this->siteIdentifier = $siteIdentifier;
		$this->siteConfiguration = $siteConfiguration;
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
	 * Prefix des aktuellen RouteEnhancers holen, z.B. `api/`
	 * ```
	 * \nn\rest::Endpoint()->getApiUrlPrefix()
	 * ```
	 * @ToDo: Echten Präfix anhand ausgewählter siteConfig ermitteln
	 * @return string
	 */
	public function getApiUrlPrefix() {
		$this->initialize();
		return 'api/';
	}

	/**
	 * Endpoint mit Klassenname und Methode für ReqType, Controller und Methode finden
	 * ```
	 * \nn\rest::Endpoint()->find( 'get', 'controller', 'action' );
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
		$routes = $classMap['routes'][$reqType] ?? [];
		foreach ($routes as $route) {
			if (preg_match($route['match'], $path, $matches)) {
				$args = array_slice($matches, 2);
				$route['arguments'] = array_combine($route['arguments'], $args);
				return $route;
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
		$cacheIdentifier = 'nnrestapi_endpoints_' . $this->siteIdentifier;

		if ($cache = $this->classMapCache) return $cache;

		if (!\nn\t3::BackendUser()->isLoggedIn() && $cache = \nn\t3::Cache()->read( $cacheIdentifier )) {
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
			$methodNameRegex = '/(' . join('|', $this->supportedMethodPrefixes) . ')(.*)(Action)/i';

			// Der RegEx, der für das parsen der `@api\route ...` Annotation verwendet wird, z.B. `@api\route GET some/path/...`
			$routeAnnotationRegex = '/((' . join('\|?|', $this->supportedMethodPrefixes) . ')*)\s*\/*(.*)/i';


			foreach ($methods as $method) {

				$methodReflection = new \ReflectionMethod( $className, $method->name );

				// Annotations parsen, z.B. nach `@api\access`
				$annotations = \Nng\Nnhelpers\Helpers\AnnotationHelper::parse($method->getDocComment(), self::ANNOTATION_NAMESPACE)['@'] ?? [];

				// Route definiert? z.B. `@api\route /test/this/{so}/{uid}/so` oder `@api\route /test/eins/zwei`
				$route = $annotations['route'] ?? '';

				$reqTypes = [];
				$action = '';
				$routeRegex = '';
				$arguments = [];

				if ($route) {

					// `@api\route ...` wurde als Annotation definiert
					preg_match($routeAnnotationRegex, $route, $matches);

					// get`, `post`... kommt aus `@api\route GET|POST ...`
					$reqTypes = $matches[1] ? \nn\t3::Arrays(strtolower($matches[1]))->trimExplode('|') : $this->supportedMethodPrefixes;
					
					$route = $matches[3];
					
					// `path/to/{uid?}/{test?}` => `path/to[/]?([^/]*)[/]?([^/]*)`
					$pattern = preg_replace('/\/\{[^\?\}]*\?\}/i', '[/]*([^/]*)', $route);

					// `path/to/{uid}/{test}` => `path/to/([^\/]*)/([^\/]*)`
					$pattern = preg_replace('/\{[^\}]*\}/i', '([^/]*)', $pattern);

					$routeRegex = '/(.*)' . str_replace('/', '\/', $pattern) . '$/i';

					// Argumente ermitteln
					preg_match_all( '/\{([^\?\}]*)[\?]*\}/i', $route, $matches );
					$routeArguments = $matches[1] ?? [];
					$routeArguments = array_combine( $routeArguments, array_fill(0, count($routeArguments), '') );

				} else if (preg_match($methodNameRegex, $method->name, $matches)) {

					// Standard-Name für die Methode wurde verwendet (`getSomethingAction`)

					// `get`, `post`... kommt aus Methoden-Name Prefix
					$reqTypes[] = strtolower( $matches[1] );

					// `IndexSomewhere` => `indexSomewhere`
					$action = lcfirst($matches[2]);

				} else {

					// Keines von beiden? Dann ignorieren.
					continue;
				}

				// Methode parsen: Welche DI-Objects / Models erwartet die Methode?
				$methodArgs = [];
				if ($arguments = $methodReflection->getParameters()) {
					foreach ($arguments as $argument) {
						if ($expectedClass = $argument->getClass()) {
							$methodArgs[] = [
								'class' => $expectedClass->getName()
							];
						}
					}
				}
				
				// Wer darf die Methode aufrufen?
				$accessList = $this->parseAccessRights( $annotations['access'] );
				
				$routeDefinition = !$route ? false : [
					'path' 	=> $route,
					'match'	=> $routeRegex,
					'args' 	=> $routeArguments,
				];

				$routeData = [
					'route'			=> $routeDefinition,
					'access'		=> $accessList,
					'slug'			=> $slug,
					'method'		=> $method->name,
					'class' 		=> $className,
					'controller' 	=> $classShortName,
					'action' 		=> $action,
					'args'			=> $methodArgs,
				];

				$path = ltrim($route ?: "{$classShortName}/{$action}", '/');

				foreach ($reqTypes as $reqType) {
					if (!isset($routingMap[$reqType])) $routingMap[$reqType] = [];
					if (!isset($routingMap[$reqType][$path])) $routingMap[$reqType][$path] = [];
					$routingMap[$reqType][$path][$slug] = $routeData;
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

		$composerClassLoader = \TYPO3\CMS\Core\Core\ClassLoadingInformation::getClassLoader();
		$psr4prefixes = $composerClassLoader->getPrefixesPsr4();

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

	/**
	 * Funktioniert wie `getClassMap()` – ergänzt aber noch die Kommentare aus
	 * dem DocComment der einzelnen Klassen-Methoden. Wird verwendet für das Backend-Modul
	 * und die Doku der einzelnen Endpoints im Backend.
	 * ```
	 * \nn\rest::Endpoint()->getClassMapWithDocumentation();
	 * ```
	 * @return array
	 */
	public function getClassMapWithDocumentation( &$arr = null ) {
		if ($arr === null) {
			$arr = $this->getClassMap();
		}
		foreach ($arr as &$v) {
			if (!is_array($v)) continue;
			if (($className = $v['class'] ?? false) && ($methodName = $v['method'] ?? false)) {
				$method = new \ReflectionMethod( $className, $methodName );
				$comment = \Nng\Nnhelpers\Helpers\AnnotationHelper::parse($method->getDocComment(), self::ANNOTATION_NAMESPACE);
				$v += $comment;
				$v['annotations'] = $v['@'] ?? [];
				continue;
			}
			$this->getClassMapWithDocumentation( $v );
		}
		return $arr;
	}

	/**
	 * Parsed eine `@access ...` Angabe zu den Zugriffsrechten.
	 * Gibt ein "schönes" Array mit `fe_users`, `be_users` etc. zurück.
	 * 
	 * @return array
	 */
	public function parseAccessRights( $accessStr = '' ) {

		$accessList = [];

		// Definition der Gruppen aus der siteConfig-Yaml
		$groupConfigurations = $this->siteConfiguration['nnrestapi']['accessGroups'] ?? [];

		if (preg_match_all('/([^\[,]*)(\[([^\]]*)\])?/i', $accessStr, $matches)) {
			foreach ($matches[1] as $n=>$v) {
				$v = trim($v);
				if (!$v) continue;
				
				// Liste der uids oder usernamen in den Klammern, z.B. `fe_users[...]`
				$userList = \nn\t3::Arrays($matches[3][$n] ?? '')->trimExplode();
				
				// Keine Einschränkungen auf bestimmte uids oder usernamen bedeutet: ALLE dieses Typs dürfen!
				if (!count($userList)) {
					$userList = ['*'=>'*'];
				}

				// `config` nehmen aus der YAML site-Konfiguration
				if ($v == 'config') {
					foreach ($userList as $configKey) {
						$accessStrFromConfig = $groupConfigurations[$configKey] ?? false;
						if (!$accessStrFromConfig) continue;
						$parsedAccessList = $this->parseAccessRights( $accessStrFromConfig );
						$accessList = \nn\t3::Arrays($accessList)->merge($parsedAccessList, true, true);
					}
					continue;
				}

				// `be_users` oder `fe_users`
				if (!$accessList[$v]) {
					$accessList[$v] = [];
				}

				$accessList[$v] = array_merge( $accessList[$v], $userList );
			}
		}

		$accessList = $this->convertUserNamesToUids( $accessList );

		return $accessList;
	}


	/**
	 * `username` in `uid` umwandeln.
	 * 
	 * @return array
	 */
	public function convertUserNamesToUids( $accessList ) {

		// Diese Tabellen werden für die Konvertierung berücksichtigt
		$tables = ['fe_users'=>'username', 'be_users'=>'username', 'public'=>''];

		foreach ($tables as $table=>$field) {
			if (!isset($accessList[$table])) continue;

			// Bereits ALLE User erlaubt? z.B. über `@access fe_users` ohne `[1,2,...]`
			$anyUserAllowed = $accessList[$table]['*'] ?? false;

			// Leeres Array bedeutet hier: In der `@access`-Annotation wurden ALLE User der Gruppe erlaubt
			if ($anyUserAllowed || count($accessList[$table]) == 0) {
				$accessList[$table] = ['*'=>'*'];
				continue;
			}

			foreach ($accessList[$table] as $k=>$uidOrUsername) {
				if (is_numeric($uidOrUsername)) continue;
				$user = $this->userCache[$table][$uidOrUsername] ?? false;
				if (!$user) $user = \nn\t3::Db()->findOneByValues($table, [$field=>$uidOrUsername]);
				if (!$user) {
					unset($accessList[$table][$k]);
					continue;
				};
				$this->userCache[$table][$uidOrUsername] = $user;
				$accessList[$table][$k] = $user['uid'];
			}
			$vals = $accessList[$table];
			$accessList[$table] = array_combine( $vals, $vals );
		}

		return $accessList;
	}
}