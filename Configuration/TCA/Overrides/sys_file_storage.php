<?php


$tmpColumns = [
	'nnrestapi_config' => [	
		'label' => 'Rest-Api Configuration',
		'description' => '',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'insertEmpty' => true,
			'itemsProcFunc' => 'nn\t3\Flexform->insertOptions',
			'typoscriptPath' => 'plugin.tx_nnrestapi.settings.sysFileStoragePresets'
		],
	],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'sys_file_storage',
	$tmpColumns
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'sys_file_storage',
	'--div--;RestAPI,nnrestapi_config',
    '',
	'after:processingfolder'
);
