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
	
	/**
	 * Get the current user that was passed via HTTP Basic Auth.
	 * 
	 * The HTTP Basic Auth users are defined either
	 * - globally in the extension manager (EXT configuration).
	 * - in the TCA of the individual `fe_user.nnrestapi_apikey`-entries
	 *  
	 * The most primitive way to make an authenticated call via HTTP Basic Auth would
	 * be sending a JavaScript or PHP request in the style: 
	 * `https://username:password@www.mywebsite.com/api/endpoint`
	 * 
	 * If `username` and `password` are correct, this method will return the username.
	 * If no credentials were passed or they are invalid, the method will return `FALSE`.
	 * 
	 * ```
	 * \nn\rest::Auth()->getHttpBasicAuthUser()
	 * ```
	 * @return string|boolean
	 */
	public function getHttpBasicAuthUser() {

		$credentials = \nn\t3::Request()->getBasicAuth();
		if (!$credentials) return false;

		$username = $credentials['username'];
		$apiKey = $credentials['password'];

		// username or password empty? Abort.
		if (!trim($username) || !trim($apiKey)) {
			return false;
		}

		// Abort, if the default user from the Extension Manager was passed
		if ($username == 'examplefeUserName') {
			return false;
		}

		// Get users defined in the Extension Manager
		$userlist = \nn\t3::Arrays( \nn\t3::Environment()->getExtConf('nnrestapi', 'apiKeys') )->trimExplode("\n");
		$userlistByAuth = array_combine( $userlist, $userlist );
		
		// User was defined in Extension Manager? Then return username
		if ($user = $userlistByAuth["{$username}:{$apiKey}"] ?? false) {
			return $username;
		}

		// ... or is it a fe_user? Credentials have already been checked by Authenticator/BasicAuth
		if ($feUser = \nn\t3::FrontendUser()->get()) {
			if ($feUser['nnrestapi_apikey'] == $apiKey) {
				return $username;
			}
		}

		return false;
	}
}