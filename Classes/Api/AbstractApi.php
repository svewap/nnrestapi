<?php
namespace Nng\Nnrestapi\Api;

use Nng\Nnrestapi\Mvc\Response;
use Nng\Nnrestapi\Mvc\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Nnrestapi
 * 
 */
abstract class AbstractApi {

	/**
	 * @var int
	 */
	public $languageUid;
	
	/**
	 * @var Request
	 */
	protected $request;
	
	/**
	 * @var Response
	 */
	protected $response;

	/**
	 * @return  Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * @param   Request  $request  
	 * @return  self
	 */
	public function setRequest( &$request ) {
		$this->request = $request;
		return $this;
	}

	/**
	 * @return  Response
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * @param   Response  $response  
	 * @return  self
	 */
	public function setResponse( &$response ) {
		$this->response = $response;
		return $this;
	}
	
	/**
	 * 
	 * @return void
	 */
	public function initializeObject() {}
	
	/**
	 * Called AFTER all other properties have been set
	 * (e.g. the `reponse`, `request`, `languageUid` etc.)
	 * Can be overriden by classes extending this class
	 * 
	 * @return void
	 */
	public function afterInitialization() {}

	/**
	 * Determines the current language requested by the frontend.
	 * 
	 * This method can be overriden with custom methods in your own endpoint if you wish 
	 * to implement your own logic.
	 * 
	 * @return int
	 */
	public function determineLanguage( $endpoint = [] ) {

		$localizationSettings = $this->request->getSettings()['localization'] ?? [];

		// localization not configured? Don't localize
		if (!$localizationSettings) {
			return 0;
		}
        // localization enabled, but locally disabled by `@Api\Localize(FALSE)` Annotation? Don't localize
        if ($localizationSettings['enabled'] && ($endpoint['localize'] ?? true) === false) {
            return 0;
        }
        // localization disabled, and not enabled locally by `@Api\Localize(TRUE)` Annotation? Don't localize
        if (!$localizationSettings['enabled'] && ($endpoint['localize'] ?? false) !== true) {
            return 0;
        }
		// `L=..` parameter passed in GET-Request?
		$L = \nn\t3::Request()->GP()['L'] ?? '';
		if ($L != '') {
			return intval($L);
		}

		// language from route / url-path?
		if ($languageId = $this->request->getMvcRequest()->getAttribute('language')->getLanguageId()) {
			if ($languageId > 0) {
				return $languageId;
			}
		}

		// `accept-language` or `x-language` passed in Request-Header?
		if ($acceptedLanguageUid = $this->request->getAcceptedLanguageUid()) {
			return $acceptedLanguageUid;
		}

		return 0;
	}

	/**
	 * Set default headers for response, e.g. `Cache-Control`
	 * 
	 * @param array $endpoint
	 * @return void
	 */
	public function setDefaultHeaders( $endpoint = [] ) 
	{
		// set `Cache-Control: max-age={value}` header
		$maxAge = $endpoint['maxAge'] ?? false;
		if ($maxAge !== false) {
			$this->response->setMaxAge( $maxAge );
		}
	}

	/**
	 * Checks, if the current frontend/backend user has privileges to call
	 * the endpoint. This method can be overriden with custom methods in your
	 * own endpoint if you wish to implement your own logic.
	 * 
	 * See `Annotations\Access` for more information.
	 * 
	 * @param array $endpoint
	 * @return boolean
	 */
	public function checkAccess ( $endpoint = [] ) 
	{
		// @Api\Access("ip_users[...]") - ANY user with given IP will be able to access
		if ($ipUserList = $endpoint['access']['ip_users'] ?? false) {
			if (GeneralUtility::cmpIP( $this->request->getRemoteAddr(), join(',', $ipUserList ))) {
				return true;
			}
		}
		
		// @Api\Access("ip[...]") - ONLY fe_users, be_users etc. with given IP will be able to access
		if ($ipList = $endpoint['access']['ip'] ?? false) {
			if (!GeneralUtility::cmpIP( $this->request->getRemoteAddr(), join(',', $ipList ))) {
				return false;
			}
		}
		
		// @Api\Access("public") - ALWAYS returns true, the endpoint may be accessed by anybody
		if ($endpoint['access']['public'] ?? false) {
			return true;
		}
		
		// @Api\Access("be_admins") will grant access only to logged in backend-admins
		if (\nn\t3::BackendUser()->isAdmin() && ($endpoint['access']['be_admins'] ?? false)) {
			return true;
		}

		// @Api\Access("be_users") will grant access only to logged in backend-users (and admins)
		if (\nn\t3::BackendUser()->get() && \nn\t3::BackendUser()->get()->user && ($endpoint['access']['be_users'] ?? false)) {
			return true;
		}
		
		// @Api\Access("api_users") and @Api\Access("api_users[name]") will grant access to users defined in the Extension Manager / EXT-Configuration OR fe_user
		if ($basicAuthUser = \nn\rest::Auth()->getHttpBasicAuthUser()) {
			if ($endpoint['access']['api_users']['*'] ?? false) {
				return true;
			}
			if (in_array($basicAuthUser, $endpoint['access']['api_users'] ?? [])) {
				return true;
			}
		}

		// @Api\Access("fe_users") and @Api\Access("api_users[name]") checks for certain fe_users
		$feUser = \nn\t3::FrontendUser()->get();
		
		if ($feUser && $endpoint['access']['fe_users'] ?? false) {
			if ($endpoint['access']['fe_users']['*'] ?? false) {
				return true;
			}
			if ($endpoint['access']['fe_users'][$feUser['uid']] ?? false) {
				return true;
			}
		}			

		// @Api\Access("fe_groups") and @Api\Access("fe_groups[name]") checks for certain fe_user_groups
		if ($endpoint['access']['fe_groups'] ?? false) {
			if ($endpoint['access']['fe_groups']['*'] ?? false) {
				return true;
			}
			if (\nn\t3::FrontendUser()->isInUserGroup( $endpoint['access']['fe_groups'] )) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks for security issues, e.g. if the IP was blocked
	 * or the user has exceeded the maximum number of requests
	 * 
	 * See `Annotations\Security\*` for more information.
	 * 
	 * @return boolean
	 */
	public function checkSecurity ( $endpoint = [] ) 
	{
		$checker = \nn\rest::Security( $this->request );

		// execute security checks defined in the TypoScript-setup
		if ($defaultSecuritySettings = $this->request->getSettings()['security']['defaults'] ?? []) {
			foreach ($defaultSecuritySettings as $n=>$className ) {
				if (!\nn\t3::call( $className )) {
					return false;
				}
			}
		}
		// execute security checks defined by Annotations
		$checkList = $endpoint['security'] ?? [];

		foreach ($checkList as $method=>$params) {
			if (!$checker->{$method}( $params )) {
				return false;
			}
		}
		return true;
	}
}
