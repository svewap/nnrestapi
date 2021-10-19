<?php
namespace Nng\Nnrestapi\Domain\Repository;

class ApiTestRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

    public function initializeObject() {

        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage( false );
        $querySettings->setIgnoreEnableFields( true );
        $querySettings->setIncludeDeleted( true );
        $this->setDefaultQuerySettings( $querySettings );

        // \nn\t3::debug( $this->persistenceManager );
        //\nn\t3::Db()->ignoreEnableFields( $this, true, true );
    }
}
