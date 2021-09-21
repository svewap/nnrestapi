<?php

$tmpColumns = [
	'nnrestapi_jwt' => [	
		'label' => 'JWT Token',
		'config' => [
			'type' => 'input',
            'size' => 30,
		],
	],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'fe_users',
	$tmpColumns
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'fe_users',
    '--div--;RestAPI,nnrestapi_jwt',
    '',
	''
);