<?php 

namespace Nng\Nnrestapi\Utilities;

/**
 * Helper für die Authentifizierungen von Frontend-Usern.
 * 
 */
class Auth extends \Nng\Nnhelpers\Singleton {

	/**
	 * List of all registered authenticators
	 * @var array
	 */
	private $authenticators = [];

	/**
	 * Einen neuen Authenticator registrieren.
	 * 
	 * Kommt in die `ext_localconf.php` der Extension, die eine Metode zum Authentifizieren 
	 * bereitstellen soll. Dazu muss die Extension in `ext_emconf.php` eine Abhängigkeit 
	 * zu `nnrestapi` definiert sein.
	 * 
	 * __Parameter:__
	 * 
	 * `priority` 	Die Authenticator werden von der höchsten zur niedrigsten 
	 * 				Priority durchlaufen. 
	 * 
	 * `className`	Klassen-Name des Authenticators
	 * 
	 * ```
	 * \nn\rest::Auth()->register([
	 * 	'priority' 	=> '70',
	 * 	'className'	=> \Nng\Nnrestapi\Authenticator\Jwt::class
	 * ]);
	 * ```
	 * @return self
	 */
	public function register( $options = [] ) {

		$priority = $options['priority'] ?? max(array_keys($this->endpoints));
		$this->authenticators[$priority] = $options;
		krsort( $this->authenticators );

		return $this;
	}

	/**
	 * Alle registrierten Authenticators holen
	 * ```
	 * \nn\rest::Auth()->getAll()
	 * ```
	 * @return array
	 */
	public function getAll() {
		return $this->authenticators;
	}
	
}