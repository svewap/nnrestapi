<?php 

namespace Nng\Nnrestapi\Utilities;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Http\ApplicationType;

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
	public function databaseTableExists( $tableName = '') 
	{
		$connection = \nn\t3::Db()->getConnection();

		$serverVersion = $connection->getServerVersion();
		$isPostgres = stripos($serverVersion, 'PostgreSQL') !== false;
		
		if ($isPostgres) {
			$result = $connection->fetchAllAssociative("
				SELECT 
					COUNT(table_name) 
				FROM 
					information_schema.tables 
				WHERE 
					table_schema LIKE 'public' 
					AND table_type LIKE 'BASE TABLE' 
					AND table_name = '{$tableName}'
				");
			return $result > 0;
		}

		if ($connection->fetchAllAssociative("SHOW TABLES like '{$tableName}'")) {
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
	public function sessionTableExists() 
	{
		$tableName = \Nng\Nnrestapi\Utilities\Session::TABLENAME;
		return $this->databaseTableExists( $tableName );
	}
	
	/**
	 * Return list of defined languages from the YAML site configuration.
	 * Default key is the `languageId`, can be overriden with `$key` parameter.
	 * 
	 * ```
	 * \nn\rest::Environment()->getLanguages();
	 * \nn\rest::Environment()->getLanguages('iso-639-1');
	 * ```
	 * @return boolean
	 */
	public function getLanguages( $key = 'languageId' ) 
	{
		$languages = \nn\t3::Settings()->getSiteConfig()['languages'] ?? [];
		return array_combine( array_column($languages, $key), array_values($languages) );
	}
	
	/**
	 * Returns TRUE if we are in the frontend context
	 * ```
	 * \nn\rest::Environment()->isFrontend();
	 * ```
	 * @return Boolean
	 */
	public function isFrontend() 
	{
		if ($request = \nn\rest::Settings()->getRequest()) {
			return ApplicationType::fromRequest($request)->isFrontend();
		}
		return false;
	}
	
}