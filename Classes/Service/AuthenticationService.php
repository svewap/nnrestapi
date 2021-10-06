<?php 

namespace Nng\Nnrestapi\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Wird nur aufgerufen bei
 * ?logintype=login
 * 
 */
class AuthenticationService extends \TYPO3\CMS\Core\Authentication\AuthenticationService {
	
	/**
	 * @var array
	 */
	protected $config;
	protected $settings;
	protected $formData;

	/**
     * Login type, used for services.
     * @var string
     */
    public $loginType = 'FE';

	var $localLoginData;
	var $prefixId = 'tx_nnrestapi';
	var $scriptRelPath = 'Classes/Service/AuthenticationService.php';
	var $extKey = 'nnrestapi';    
	var $igldapssoauth;
	var $contentElementUid;
	
	/**
	 * 	Default constructor
	 * 	POST-Daten parsen und entschlüsseln, falls erforderlich
	 */
	public function __construct() {
		die('YEAH!');
		$this->config = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][$this->extKey] ?? [];
		//$this->frontendUserRepository = \nn\t3::injectClass( \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository::class );
	}

	/**
	 * Auth-Prozess initialisieren.
	 * Wir vor allen anderen Methoden aufgerufen und prüft, ob dieser Service überhaupt die
	 * Authentifizierung durchführen kann.
	 * 
	 * Diese Methode muss `$this->loginData` setzen, damit `getUser()` und `authUser()` darauf
	 * Zugriff haben.
	 * 
	 */
	public function initAuth($mode, $loginData, $authInfo, $pObj) {
		file_put_contents('/var/www/vhosts/99grad.dev/tetronik-web2print.99grad.dev/public/typo3conf/ext/nnrestapi/Classes/Service/test.txt', 'HALLO!');
		
		/*
		// nnfelogin verwendet eine komplett eigene Verschlüsselung...
		$postData = GeneralUtility::_POST($this->extKey);
		$this->contentElementUid = $postData['uid'];

		// ... in einem eigenen Container
		$encryptedFormData = $postData[$this->contentElementUid] ?: [];
		$this->formData = $this->encryptionService->decryptRequestArguments( $encryptedFormData );

		// Nach dem Entschlüsseln der Daten: $this->loginData für nachfolgende Methoden setzen
		$this->loginData = array_merge($loginData, [
			'uname'			=>	$this->formData['email'],
			'uident'		=>	$this->formData['pw'],
			'uident_text'	=>	$this->formData['pw'],
		]);
		
		$this->settings = \nn\t3::Settings()->getMergedSettings($this->extKey, $this->contentElementUid);
		*/
	}
	
	/**
	 * 	getUser() wird in der auth-Service-Abfolge als erstes aufgerufen.
	 * 	Sucht einen User in der fe_user-Tabelle anhand seines Usernamens, E-Mail, Mitgliedsnummer etc.
	 *
	 * @return mixed user array or false
	 * @throws UnsupportedLoginSecurityLevelException
	 */
	public function getUser() {
/*
		// Der Username
		$uname = $this->loginData['uname'];

		// Felder, die als Username dienen können (z.B. 'username', 'email', 'member_uid')
		$usernameFields = \nn\t3::Arrays( $this->settings['usernameFields'] )->trimExplode();
		if (!$usernameFields) \nn\t3::Exception('nnfelogin: Keine usernameFields im TypoScript definiert.');

		$authOptions = [];
		foreach ($usernameFields as $field) {
			$authOptions[$field] = $uname;
		}

		$authOptions = [
			'nnrestapi_jwt' => \Nng\Nnrestapi\Services\AuthentificationService::getBearerToken()
		];
		$users = \nn\t3::Db()->findByValues( 'fe_users', $authOptions, true, true );
		
		return array_shift($users);
		*/
		return [];
	}


	/**
	 * 	authUser() wird in der auth-Service-Abfolge als zweites aufgerufen.
	 * 	Prüft, ob User mit eingegebenen Credentials authentifiziert werden
	 * 	kann.
	 * 
	 *	Rückgabe:
	 * 	true 	= Authentifizierung durch diesen Service war erfolgreich
	 * 	200 	= Authentifizierung erfolgreich, keine weitere Überprüfung durch nachfolgenden Service erforderlich
	 * 	false 	= Dieser Service war korrekt, aber Authentifizierung nicht erfolgreich
	 * 	100 	= Authentifizierung nicht erfolgreich, nächster Service soll Überprüfung fortsetzen
	 * 
	 * @param array $user Data of user.
	 * @return int|false
	 */
	public function authUser(array $user): int {
/*
		// Kein Passwort übergeben? Dann ist dieser Service nicht verantwortlich.
		if (!($this->loginData['uident_text'] ?? false)) return 100;

		$extConf = \nn\t3::Settings()->getExtConf('nnfelogin');
		$authResult = true;

		return $authResult ? 200 : 100;
		*/
		return 100;
	}

}