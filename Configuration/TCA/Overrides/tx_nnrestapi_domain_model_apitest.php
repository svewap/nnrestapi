<?php

defined('TYPO3') or die();


if (\nn\t3::t3Version() < 11) {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
		'nnrestapi',
		'tx_nnrestapi_domain_model_apitest',
		'categories',
		[
			'label' => 'Kategorien',
			'exclude' => FALSE,
			'fieldConfiguration' => [
				'foreign_table_where' => ' AND sys_category.sys_language_uid IN (-1, 0) ORDER BY sys_category.title ASC',
			],
			'l10n_mode' => 'exclude',
			'l10n_display' => 'hideDiff',
		]
	);
}