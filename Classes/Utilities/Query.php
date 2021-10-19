<?php 

namespace Nng\Nnrestapi\Utilities;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

/**
 * Helper for retrieving Models from Database
 * 
 * https://docs.typo3.org/m/typo3/book-extbasefluid/master/en-us/6-Persistence/3-implement-individual-database-queries.html
 */
class Query extends \Nng\Nnhelpers\Singleton {


	protected $_className = '';

	protected $persistenceManager = null;

	protected $query = null;

	protected $returnRawData = false;

	public function __construct( $className ) {
		$this->_className = $className;
		$this->persistenceManager = \nn\t3::injectClass( \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class );
		$this->query = $this->persistenceManager->createQueryForType( $className );
		// Ignore StoragePID
		\nn\t3::Db()->ignoreEnableFields( $this->query, true );
	}

	/**
	 * Return the raw data from Database instead of the model.
	 * 
	 * ```
	 * \nn\rest::Query( $className )->getRawData( true )->findByUid( $uid );
	 * ```
	 */
	public function getRawData( $getRawData = false) {
		$this->returnRawData = $getRawData;
		return $this;
	}
	
	/**
	 * Ignore hidden entries
	 * 
	 * ```
	 * \nn\rest::Query( $className )->ignoreHidden( true )->findByUid( $uid );
	 * ```
	 */
	public function ignoreHidden( $ignoreHidden = false) {
		\nn\t3::Db()->ignoreEnableFields( $this->query, true, $ignoreHidden );
		return $this;
	}

	/**
	 * Find a Entity/Model by the `uid`.
	 * ```
	 * \nn\rest::Query( \Nng\Nnrestapi\Domain\Model\ApiTest::class )->findByUid( 1 );
	 * ```
	 * @return Model
	 */
	public function findByUid( $uid ) {

		$query = $this->query;
		$query->matching( $query->equals('uid', intval($uid)) );
		$result = $query->execute( $this->returnRawData );
\nn\t3::debug($query);
		foreach ($result as $row) return $row;
	}
}