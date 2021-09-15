<?php 

namespace Nng\Nnrestapi\Utilities;

/**
 * Helper für häufig genutzte Api-Funktionen.
 * 
 */
class Endpoint extends \Nng\Nnhelpers\Singleton {

	/**
	 * Liste aller registrierten Endpoints
	 * @var array
	 */
	private $endpoints = [];

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
	 * Mapping aller Klassen und Methoden der Endpoints holen.
	 * ```
	 * \nn\rest::Endpoint()->getClassMap()
	 * ```
	 * @return array
	 */
	public function getClassMap() {

		if ($cache = \nn\t3::Cache()->read( 'nnrestapi_endpoints' )) {
			//return $cache;
		}

		$endpoints = $this->getAll();
		$endpointsBySlug = \nn\t3::Arrays( $endpoints )->key('slug')->removeEmpty();
		
		$namespaces = array_column( $endpoints, 'namespace' );
		$registeredClasses = [];
		foreach(get_declared_classes() as $name) {
//			echo "-- {$name}\n";
			$found = array_filter($namespaces, function ($classPrefix) use ($name) {
				
				return strpos( $name, $classPrefix ) !== false;
			});
			if (!$found) continue;
			$registeredClasses[] = $name;
		}

		print_r(get_declared_classes());

		foreach ($endpoints as $conf) {
			$controllerClassName = rtrim($conf['namespace'], '\\') . '\\';
			
			//echo $controllerClassName."\n";
		}

		//echo method_exists( \Nng\Nnrestapi\Api\Test::class, 'getIndexAction') ? 'JA' : 'NEE';

//		print_r($registeredClasses);
/*
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
*/
		$map = ['nice'=>'OK!'];

		return \nn\t3::Cache()->write( 'nnrestapi_endpoints', $map );
	}

}