<?php

	defined('TYPO3') or die();

    $fields = [
        'title' => [
            'exclude' => 1,
            'label'	=> 'Title',
            'config'  => [
                'type'	  => 'input',
                'default'  => '',
            ]
        ],
        'files' => [
            'exclude' => 1,
            'label'	=> 'Files',
            'config'  => \nn\t3::TCA()->getFileFieldTCAConfig('files'),
        ],
	];

	return [
		'ctrl' => [
			'title'	=> 'LLL:EXT:apitest/Resources/Private/Language/locallang_db.xlf:tx_apitest_domain_model_entry',
			'label' => 'title',
			'searchFields' => '',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'dividers2tabs' => TRUE,    
            'iconfile' => 'EXT:apitest/Resources/Public/Icons/Extension.svg',
            'languageField' => 'sys_language_uid',
            'transOrigPointerField' => 'l10n_parent',
            'transOrigDiffSourceField' => 'l10n_diffsource',
            'delete' => 'deleted',
            'enablecolumns' => [
                'disabled' => 'hidden',
                'starttime' => 'starttime',
                'endtime' => 'endtime',
                'fe_group' => 'fe_group',
            ],   
		],

		'interface' => [],
		
		'types' => [
			'0' => ['showitem' => '
                --div--;Basics,
                    --palette--;;1,
                    sys_language_uid,l10n_parent,l10n_diffsource,
                    ' . join(',', array_keys($fields)) . ',
                --div--;Access,
				    --palette--;;5,
                '],
		],

		'palettes' => [
            '5' => ['showitem' => 'hidden, starttime, endtime,--linebreak--, fe_group'],
        ],

        'columns' => \nn\t3::TCA()->createConfig(
			'tx_apitest_domain_model_entry',
			true,
			$fields,
		),

	];