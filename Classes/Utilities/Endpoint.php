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
	 * 
	 * @return array
	 */
	public function getAll() {
		return $this->endpoints;
	}

}