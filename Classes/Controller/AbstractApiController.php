<?php
declare(strict_types = 1);

namespace Nng\Nnrestapi\Controller;

/**
 * AbstractApiController
 * 
 * All Api-Controllers should extend this Controller.
 * 
 */
abstract class AbstractApiController {

	/**
	 * @var \Nng\Nnrestapi\Mvc\Request
	 */
	public $request;

	/**
	 * @var array
	 */
	public $settings;

	/**
	 * @var \Nng\Nnrestapi\Mvc\Response
	 */
	public $response;

	/**
	 * Main action called by MiddleWare `PageResolver` 
	 */
	public function indexAction() {}


	/**
	 * @return  \Nng\Nnrestapi\Mvc\Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * @param   \Nng\Nnrestapi\Mvc\Request  $request  
	 * @return  self
	 */
	public function setRequest($request) {
		$this->request = $request;
		return $this;
	}

	/**
	 * @return  \Nng\Nnrestapi\Mvc\Response
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * @param   \Nng\Nnrestapi\Mvc\Response  $response  
	 * @return  self
	 */
	public function setResponse($response) {
		$this->response = $response;
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
}
