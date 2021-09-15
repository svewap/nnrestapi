<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
	function( $extKey )
	{

		// Utilities für `\nn\rest::Beispiel()->method()` registrieren
		$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extKey);		
		require_once($extPath . 'Classes/Utilities/nnrest.php');

		// Plugin registrieren
		\nn\t3::Registry()->configurePlugin( 'Nng\Nnrestapi', 'api', 
            [\Nng\Nnrestapi\Controller\ApiController::class => 'index'],
            [\Nng\Nnrestapi\Controller\ApiController::class => 'index']
        );

		// Globalen Namespace {rest:...} registrieren für ViewHelper
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['rest'] = ['Nng\\Nnrestapi\\ViewHelpers'];

		// Eigener RouteEnhancer
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers']['NnrestapiEnhancer'] = \Nng\Nnrestapi\Routing\Enhancer\NnrestapiEnhancer::class;

		// Endpoint		
		\nn\rest::Endpoint()->register([
			'priority' 	=> '0',
			'slug' 		=> 'nnrestapi',
			'namespace'	=> 'Nng\Nnrestapi\Api'
		]);
	},
'nnrestapi');
