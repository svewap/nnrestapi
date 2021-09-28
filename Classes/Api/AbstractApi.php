<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Nng\Nnrestapi\Mvc\Request;

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
	 * Prüft, ob aktueller fe_user Rechte hat, den gewünschten Endpoint aufzurufen.
	 * Als nicht-eingeloggter User dürfen nur Methoden aufgerufen werden, die in 
	 * den Annotations als `@access public` markiert sind.
	 * 
	 * Folgende Rechte existieren für `@access ...`:
	 * 
	 * | ---------------------------------- | ----------------------------------------------------- |
	 * | Annotation 						| Rechte: Aufrufbar von...							 	|
	 * | ---------------------------------- | ----------------------------------------------------- |
	 * | @access public						| jedem, ohne Authentifizierung	 					 	|
	 * | @access fe_users					| jedem eingeloggten Frontend-User	 				 	|
	 * | @access fe_users[1]				| Nur eingeloggten Frontend-User mit uid 1	 		 	|
	 * | @access fe_users[david]			| Nur eingeloggten Frontend-User mit username `david`	|
	 * | @access be_users					| jedem eingeloggten Backend-User	 			 	    |
	 * | @access be_admins					| jedem eingeloggten Backend-Admin	 	 			    |
	 * | @access fe_group[1,2]				| fe_user-group mit uid 1 und 2	 				        |
	 * | @access fe_group[api]				| fe_user-group 'api'				 			        |
	 * | @access config[myconf]				| Yaml config für die site/API verwenden				|
	 * | ---------------------------------- | ----------------------------------------------------- |
	 * 
	 * @return boolean
	 */
	public function checkAccess ( $endpoint = [] ) {

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
