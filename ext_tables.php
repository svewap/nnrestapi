<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
	function( $extKey )
	{
		
		// Allow table
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_nnrestapi_domain_model_apitest');

		// Backend-Module registrieren
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
			\nn\t3::Registry()->getVendorExtensionName($extKey),
			'web',
			'mod1',
			'',
			\nn\t3::Registry()->parseControllerActions([
				\Nng\Nnrestapi\Controller\ModController::class => 'index, kickstart, readme',
			]),
			[
				'access'	=> 'user,group',
				'icon'	 	=> 'EXT:nnrestapi/Resources/Public/Icons/Extension.svg',
				'labels'	=> 'LLL:EXT:nnrestapi/Resources/Private/Language/locallang_mod1.xml',
				'navigationComponentId' => '',
				'inheritNavigationComponentFromMainModule' => false,
			]
		);
    },
'nnrestapi');
