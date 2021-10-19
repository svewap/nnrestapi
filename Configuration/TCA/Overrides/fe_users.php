<?php


$tmpColumns = [
	'nnrestapi_apikey' => [	
		'label' => 'Rest-Api Key',
		'description' => 'Use this key for a BasicAuth authentication when making requests to API (https://{username}:{apiKey}@domain.com)',
		'config' => [
			'type' => 'input',
		],
	],
	'nnrestapi_admin' => [	
		'label' => 'Admin-Mode: Show hidden records',
		'description' => 'The user will see hidden records and relations like a backend-admin',
		'config' => [
			'type' => 'check',
			'renderType' => 'checkboxToggle',
		],
	],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'fe_users',
	$tmpColumns
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'fe_users',
	'--div--;RestAPI,nnrestapi_apikey,nnrestapi_admin',
    '',
	'after:subgroup'
);
