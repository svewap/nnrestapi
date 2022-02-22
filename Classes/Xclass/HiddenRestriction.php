<?php

namespace Nng\Nnrestapi\Xclass;

use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction as BaseHiddenRestriction;

use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;

/**
 * 
 */
class HiddenRestriction extends BaseHiddenRestriction {

	/**
	 * 
	 */
	public function buildExpression(array $queriedTables, ExpressionBuilder $expressionBuilder): CompositeExpression
	{
		if (\nn\t3::Environment()->isFrontend()) {
			if (!\nn\rest::Settings()->getQuerySettings('ignoreEnableFields')) {
				return parent::buildExpression( $queriedTables, $expressionBuilder );
			}
		}
		return $expressionBuilder->andX([]);
	}
}