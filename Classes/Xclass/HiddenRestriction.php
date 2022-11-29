<?php

namespace Nng\Nnrestapi\Xclass;

use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction as BaseHiddenRestriction;

use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Http\ApplicationType;

/**
 * 
 */
class HiddenRestriction extends BaseHiddenRestriction 
{
	/**
	 * XCLASSes \TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction
	 * Allows retrieving hidden records in a frontend context using the
	 * `@Api\IncludeHidden` annotation.
	 * 
	 * see: https://bit.ly/3RMMZsk
	 * 
     * @param array $queriedTables
     * @param ExpressionBuilder $expressionBuilder
     * @return CompositeExpression
	 */
	public function buildExpression(array $queriedTables, ExpressionBuilder $expressionBuilder): CompositeExpression
	{
		if (\nn\rest::Environment()->isFrontend()) {
			if (\nn\rest::Settings()->getQuerySettings('ignoreEnableFields')) {
				return $expressionBuilder->andX([]);
			}
		}
		return parent::buildExpression( $queriedTables, $expressionBuilder );
	}

}