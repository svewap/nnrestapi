<?php

namespace Nng\Nnrestapi\Mvc;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;

class Request {

    /**
     * @var array
     */
    protected $body;

    /**
     * @var string
     */
    protected $rawBody;
   
    /**
     * @var array
     */
    protected $endpoint = [];

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $feUser = [];

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var array
     */
    protected $uploadedFiles = [];

    /**
     * @var string
     */
    protected $acceptedLanguage = '';

    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Request
     */
    protected $mvcRequest;


	/**
	 * Wrapper für den Standard-Typo3-Request.
	 * 
	 * Wandelt den `TYPO3\CMS\Extbase\Mvc\Request` in eine für unsere Zwecke
	 * bessere / einfachere Form um mit normalisierten gettern/settern.
	 * 
	 */
    public function __construct( $request ) {

        $this->setArguments( $request->getQueryParams() );
        $this->setMvcRequest( $request );

		$this->rawBody = $request->getBody()->getContents();
		// Bei `multipart/form-data`: JSON befindet sich an anderer Stelle, weil auch Dateien/Filedata übertragen wurde
		if (!$this->rawBody && $body = $request->getParsedBody()) {
			$this->rawBody = json_decode( $body['json'] ?? '', true );
			$this->body = is_array($this->rawBody) ? $this->rawBody : json_decode( $this->rawBody, true );
		} else {
			$this->body = json_decode( $this->rawBody, true ) ?: $this->rawBody ?: [];
		}

		$this->uploadedFiles = $request->getUploadedFiles() ?: [];
    }

	/**
	 * @return  arguments
	 */
	public function getUploadedFiles() {
		return $this->uploadedFiles;
	}
	
	/**
	 * @return  arguments
	 */
	public function getArguments() {
		return $this->arguments;
	}

	/**
	 * @param   arguments  $arguments  
	 * @return  self
	 */
	public function setArguments($arguments) {
		if (!is_array($arguments)) return $this;
		$this->arguments = array_merge( $this->arguments, $arguments );
		return $this;
	}

	/**
	 * @return array
	 */
	public function getServerParams() {
		return $this->mvcRequest->getServerParams();
	}
	
	/**
	 * @return string
	 */
	public function getRemoteAddr() {
		return $this->getServerParams()['REMOTE_ADDR'] ?? '';
	}
	
	/**
	 * @return  \TYPO3\CMS\Extbase\Mvc\Request
	 */
	public function getMvcRequest() {
		return $this->mvcRequest;
	}

	/**
	 * @param   \TYPO3\CMS\Extbase\Mvc\Request  $mvcRequest  
	 * @return  self
	 */
	public function setMvcRequest($mvcRequest) {
		$this->mvcRequest = $mvcRequest;
		return $this;
	}

	/**
	 * Get the accepted language from header.
	 * The header-fields to search in can be defined in the TypoScript setup.
	 * 
	 * Returns a short two-character code like `en` or `de`.
	 * 
	 * The "real" header can be more complex than we are evaluating here.
	 * e.g. `en-DE,en;q=0.9,de-DE;q=0.8,de;q=0.7,en-GB;q=0.6,en-US;q=0.5`
	 * 
	 * @return  string
	 */
	public function getAcceptedLanguage() {
		if ($lang = $this->acceptedLanguage) {
			return $lang;
		}
		if ($languageHeaders = $this->settings['localization']['languageHeader'] ?? false) {
			$languageHeaders = \nn\t3::Arrays( $languageHeaders )->trimExplode();
			$headers = $this->getHeaders();
			foreach ($languageHeaders as $headerName) {
				if ($val = $headers[$headerName] ?? false) {
					$this->acceptedLanguage = strtolower(substr($val, 0, 2));
					break;
				}
			}
		}
		return $this->acceptedLanguage;
	}

	/**
	 * Maps the requested language from header (`en-EN...`, `de-DE...`) to the languageId 
	 * defined in Typo3. Returns the requested languageUid as integer (`0`, `1`, ...).
	 * 
	 * If no language matches, the default language `0` will be returned.
	 * 
	 * Languages are defined in the backend module "sites" (or the site-config YAML)
	 * 
	 * @return  int
	 */
	public function getAcceptedLanguageUid() {
		$languagesByName = \nn\rest::Environment()->getLanguages( 'iso-639-1' );
		$acceptedLanguage = $this->getAcceptedLanguage();
		return $languagesByName[$acceptedLanguage]['languageId'] ?? 0;
	}

	/**
	 * @return  string
	 */
	public function getRawBody() {
		return $this->rawBody;
	}

	/**
	 * @param   string  $rawBody  
	 * @return  self
	 */
	public function setRawBody($rawBody) {
		$this->rawBody = $rawBody;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaders() {
		$headers = $this->mvcRequest->getHeaders();
		foreach ($headers as $k=>$arr) {
			$val = is_array($arr) ? array_pop($arr) : $arr;
			$headers[strtolower($k)] = $val;
		}
		return $headers;
	}

	/**
	 * @return string
	 */
	public function getMethod() {
		return strtolower( $this->mvcRequest->getMethod() );
	}
	
	/**
	 * @return string
	 */
	public function getPath() {
		$path = rtrim($this->mvcRequest->getUri()->getPath(), '/');
		return $path;
	}
	
	/**
	 * @return  array
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @param   array  $body  
	 * @return  self
	 */
	public function setBody($body) {
		$this->body = $body;
		return $this;
	}

	/**
	 * @return  array
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * @param   array  $settings  
	 * @return  self
	 */
	public function setSettings($settings) {
		$this->settings = $settings;
		return $this;
	}

	/**
	 * @return  array
	 */
	public function getEndpoint() {
		return $this->endpoint;
	}

	/**
	 * @param   array  $endpoint  
	 * @return  self
	 */
	public function setEndpoint($endpoint) {
		$this->endpoint = $endpoint;
		$this->setArguments( $endpoint['args'] );
		return $this;
	}

	/**
	 * @return  array
	 */
	public function getFeUserGroups() {
		if ($groupList = $this->feUser['usergroup'] ?? false) {
			return \nn\t3::FrontendUser()->resolveUserGroups( $groupList );
		}
		return [];
	}
	
	/**
	 * @return  array
	 */
	public function getFeUser() {
		return $this->feUser;
	}

	/**
	 * @param   array  $feUser  
	 * @return  self
	 */
	public function setFeUser($feUser) {
		$this->feUser = $feUser;
		return $this;
	}

	/**
	 * Returns if the feUser has the checkbox "RestApi Admin" set.
	 * This will grant additional privileges like retrieving hidden records.
	 * 
	 * @return boolean
	 */
	public function isAdmin() {
		return $this->feUser && $this->feUser['nnrestapi_admin'];
	}

}