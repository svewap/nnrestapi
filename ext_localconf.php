<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
	function( $extKey )
	{

		// Utilities für `\nn\rest::Beispiel()->method()` registrieren
		$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extKey);		
		require_once($extPath . 'Classes/Utilities/nnrest.php');

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

		// Eigender HTTP handler zum Verarbeiten von RequestMethods, die standardmäßig nicht unterstützt werden
		$GLOBALS['TYPO3_CONF_VARS']['HTTP']['handler'][] = 
			(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Nng\Nnrestapi\Middleware\NnrestapiRequestParser::class))->handler();

		// AUTH-Service Registrierung
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
			'nnrestapi_auth Auth',
			'auth',
			\Nng\Nnrestapi\Service\AuthentificationService::class,
			[
				'title' => 'Authentification service (nnrestapi)',
				'description' => 'Authentication service for login via JWT.',
				'subtype' => 'getUserFE,authUserFE,getGroupsFE',
				'available' => true,
				'priority' => 85,
				'quality' => 85,
				'os' => '',
				'exec' => '',
				'className' => \Nng\Nnrestapi\Service\AuthentificationService::class,
			]
		);
	},
'nnrestapi');
