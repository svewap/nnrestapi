<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Nng\Nnrestapi\Mvc\Request;
use Nng\Nnrestapi\Annotations as Api;

/**
 * Nnrestapi
 * 
 */
class AbstractApi {

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
	 * Checks, if the current frontend/backend user has privileges to call
	 * the endpoint. This method can be overriden with custom methods in your
	 * own endpoint if you wish to implement your own logic.
	 * 
	 * See `Annotations\Access` for more information.
	 * 
	 * @return boolean
	 */
	public function checkAccess ( $endpoint = [] ) {

		if ($ipList = $endpoint['access']['ip'] ?? false) {
			if (!GeneralUtility::cmpIP( $this->request->getRemoteAddr(), join(',', $ipList ))) {
				return false;
			}
		}

		if ($endpoint['access']['public'] ?? false) {
			return true;
		}
		
		if (\nn\t3::BackendUser()->isAdmin() && $endpoint['access']['be_admins'] ?? false) {
			return true;
		}

		if (\nn\t3::BackendUser()->get() && $endpoint['access']['be_users'] ?? false) {
			return true;
		}
				
		$feUser = \nn\t3::FrontendUser()->get();
		
		if ($feUser && $endpoint['access']['fe_users'] ?? false) {
			if ($endpoint['access']['fe_users']['*'] ?? false) {
				return true;
			}
			if ($endpoint['access']['fe_users'][$feUser['uid']] ?? false) {
				return true;
			}
		}			

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

}
