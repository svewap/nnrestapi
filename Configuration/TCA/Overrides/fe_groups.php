<?php

$tmpColumns = [
	'nnrestapi_flexform' => [	
		'label' => 'Rest-Api CMS Rechte',
		'config' => [
			'type' => 'flex',
            'ds' => [
                'default' => 'FILE:EXT:nnrestapi/Configuration/FlexForms/fe_groups.xml',
            ],
		],
	],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'fe_groups',
	$tmpColumns
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'fe_groups',
    '--div--;Rest-Api,nnrestapi_flexform',
    '',
	'after:subgroup'
);