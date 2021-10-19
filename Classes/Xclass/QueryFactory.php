<?php

namespace Nng\Nnrestapi\Xclass;

use TYPO3\CMS\Extbase\Persistence\Generic\QueryFactory as BaseQueryFactory;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * 
 */
class QueryFactory extends BaseQueryFactory {

	/**
	 * Creates a query object working on the given class name
	 *
	 * @param string $className The class name
	 * @return QueryInterface
	 */
	public function create($className): QueryInterface {

		$query = parent::create($className);

		if (!\nn\rest::Settings()->getQuerySettings('ignoreEnableFields')) {
			return $query;
		}
		
		$querySettings = $query->getQuerySettings();
		$querySettings->setIgnoreEnableFields(true);
		$querySettings->setRespectStoragePage(false);
		$query->setQuerySettings($querySettings);

		return $query;
	}
}