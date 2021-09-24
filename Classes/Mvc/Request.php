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

        $header = $this->mvcRequest->getHeaders()['accept-language'] ?? [];
        $this->acceptedLanguage = strtolower(substr($header[0] ?? '', 0, 2));
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
		$this->arguments = array_merge( $this->arguments, $arguments );
		return $this;
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
	 * @return  string
	 */
	public function getAcceptedLanguage() {
		return $this->acceptedLanguage;
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
		return $this;
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
}