<?php
namespace Nng\Nnrestapi\Routing;

use InvalidArgumentException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendGroupRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * 
 */
class NnrestapiPersistedAliasMapper extends PersistedAliasMapper
{
	public const ENHANCER_NAME = 'NnrestapiEnhancer';

	/**
     * @var bool
     */
    protected $ignoreEnablefields;

    /**
     * @param array $settings
     * @throws InvalidArgumentException
     */
    public function __construct(array $settings)
    {
        $ignoreEnablefields = $settings['ignoreEnablefields'] ?? false;
        $this->ignoreEnablefields = $ignoreEnablefields;
        parent::__construct($settings);
    }

    protected function createQueryBuilder(): QueryBuilder
    {
		die('OK');
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($this->tableName)
            ->from($this->tableName);
        if (true || $this->ignoreEnablefields) {
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        }
        else {
            $queryBuilder->setRestrictions(
                GeneralUtility::makeInstance(FrontendRestrictionContainer::class, $this->context)
            );
        }
        $queryBuilder->getRestrictions()->removeByType(FrontendGroupRestriction::class);
        return $queryBuilder;
    }
}