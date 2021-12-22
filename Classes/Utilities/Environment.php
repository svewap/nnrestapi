<?php 

namespace Nng\Nnrestapi\Utilities;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

/**
 * Helper for getting information about the installation and environment
 * 
 */
class Environment extends \Nng\Nnhelpers\Singleton 
{
	/**
	 * Return `TRUE`, if given table exists.
	 * 
	 * Only meant to be used internally with fixed tablenames. 
	 * Tablenames are not escaped – be aware of SQL-Injection
	 * if you want to use this method!
	 * 
	 * ```
	 * \nn\rest::Environment()->databaseTableExists( 'some_table' );
	 * ```
	 * @return boolean
	 */
	public function databaseTableExists( $tableName = '') {		
		if (\nn\t3::Db()->statement("SHOW TABLES like '{$tableName}'")) {
			return true;
		}
		return false;
	}

	/**
	 * Return `TRUE`, if the required tables for nnrestapi were installed.
	 * 
	 * ```
	 * \nn\rest::Environment()->sessionTableExists();
	 * ```
	 * @return boolean
	 */
	public function sessionTableExists() {
		$tableName = \Nng\Nnrestapi\Utilities\Session::TABLENAME;
		return $this->databaseTableExists( $tableName );
	}
	
}