<?php

namespace Nng\Nnrestapi\Xclass;

use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction as BaseHiddenRestriction;

use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Http\ApplicationType;

/**
 * 
 */
class HiddenRestriction extends BaseHiddenRestriction {

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
		if ($this->isFrontend()) {
			if (\nn\rest::Settings()->getQuerySettings('ignoreEnableFields')) {
				return $expressionBuilder->andX([]);
			}
		}
		return parent::buildExpression( $queriedTables, $expressionBuilder );
	}

	/**
	 * Return TRUE, if we are in a frontend context.
	 * 
	 * @return bool
	 */
	private function isFrontend () 
	{
		if (\nn\t3::t3Version() >= 11) {
			if ($request = \nn\rest::Settings()->getRequest()) {
				return ApplicationType::fromRequest($request)->isFrontend();
			}
		}
// @todo v12
return;
		return TYPO3_MODE == 'FE' && isset($GLOBALS['TSFE']) && $GLOBALS['TSFE']->id;
	}

}