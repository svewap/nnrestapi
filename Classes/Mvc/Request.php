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
    protected $arguments = [];

    /**
     * @var string
     */
    protected $acceptedLanguage = '';

    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Request
     */
    protected $mvcRequest;


    public function __construct( $request ) {
        $this->setArguments( $request->getQueryParams() );
        $this->setMvcRequest( $request );
        
        $this->rawBody = $request->getBody()->getContents();
        $this->body = json_decode( $this->rawBody, true ) ?: [];

        $header = $this->mvcRequest->getHeaders()['accept-language'] ?? [];
        $this->acceptedLanguage = strtolower(substr($header[0] ?? '', 0, 2));
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
		$this->arguments = $arguments;
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
}