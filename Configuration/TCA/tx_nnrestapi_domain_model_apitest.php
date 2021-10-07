<?php

	defined('TYPO3_MODE') || die('Access denied.');

	return [
		'ctrl' => [
			'title'	=> 'LLL:EXT:nnrestapi/Resources/Private/Language/locallang_db.xlf:tx_nnrestapi_domain_model_apitest',
			'label' => 'title',
			'enablecolumns' => [],
			//'hideTable' => true,
			'searchFields' => '',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'cruser_id' => 'cruser_id',
            'dividers2tabs' => TRUE,
            'languageField' => 'sys_language_uid',
            'transOrigPointerField' => 'l10n_parent',
            'transOrigDiffSourceField' => 'l10n_diffsource',
            'translationSource' => 'l10n_source',
            'delete' => 'deleted',
            'iconfile' => 'EXT:nnrestapi/Resources/Public/Icons/Extension.svg'
		],

		'interface' => [],
		
		'types' => [
			'0' => ['showitem' => '
                --div--;Basics,
                    --palette--;;1,
                    sys_language_uid,l10n_parent,l10n_diffsource,
                    title, 
                    files,
                --div--;Access,
                    --palette--;;2
            '],
		],

		'palettes' => [
            '1' => ['showitem' => ''],
            '2' => ['showitem' => 'hidden, starttime, endtime,--linebreak--, fe_group'],
		],
		
		'columns' => \nn\t3::TCA()->createConfig(
            'tx_nnrestapi_domain_model_apitest',
            true,
            [
                'title' => [
                    'label' => 'Title',		
                    'config' => [
                        'type' => 'input',
                        'cols' => '20',	
                        'rows' => '1',
                    ]
                ],
                'files' => [
                    'label' => 'Dateien',
                    'config' => \nn\t3::TCA()->getFileFieldTCAConfig('files')
                ],
            ]
        ),
	];