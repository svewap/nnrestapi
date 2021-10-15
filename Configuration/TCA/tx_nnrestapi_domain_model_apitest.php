<?php

	defined('TYPO3_MODE') || die('Access denied.');

	$fields = [
		'title' => [
			'label' => 'Title',		
			'config' => [
				'type' => 'input',
				'cols' => '20',	
				'rows' => '1',
			]
		],
		'image' => [
			'label' => 'Single FAL test (image)',
			'config' => \nn\t3::TCA()->getFileFieldTCAConfig('image', ['maxitems'=>1])
		],
		'files' => [
			'label' => 'ObjectStorage FAL test (files)',
			'config' => \nn\t3::TCA()->getFileFieldTCAConfig('files')
		],
		'children' => [
			'exclude' => 1,
			'label' => 'Multiple Children (children)',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_nnrestapi_domain_model_apitest',
				'foreign_field' => 'parentid',
				'foreign_table_field' => 'parenttable',
				'maxitems' => 10,
				'appearance' => [
					'collapseAll' => 1,
					'expandSingle' => 1,
				],
			],
		],            
		'child' => [
			'exclude' => 1,
			'label' => 'Single Child (child)',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_nnrestapi_domain_model_apitest',
				'maxitems' => 1,
				'appearance' => [
					'collapseAll' => 1,
					'expandSingle' => 1,
				],
			],
		],
	];


	if (\nn\t3::t3Version() >= 11) {
		$fields['categories'] = [
			'label' => 'Category test',
			'config' => [
				'type' => 'category'
			 ]
		];
	}

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
					image,
					files,
					children,
					child,
					categories,
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
			$fields,
		),
	];