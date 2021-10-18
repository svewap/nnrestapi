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

		// Authenticator registrieren
		\nn\rest::Auth()->register([
			'priority' 	=> '0',
			'className'	=> \Nng\Nnrestapi\Authenticator\Jwt::class
		]);

		// Authenticator registrieren
		\nn\rest::Auth()->register([
			'priority' 	=> '1',
			'className'	=> \Nng\Nnrestapi\Authenticator\BasicAuth::class
		]);

		// Eigender HTTP handler zum Verarbeiten von RequestMethods, die standardmäßig nicht unterstützt werden
		$GLOBALS['TYPO3_CONF_VARS']['HTTP']['handler'][] = 
			(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Nng\Nnrestapi\Middleware\RequestParser::class))->handler();
		
		// Nur für Typo3 < 11 erforderlich
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers'][\Nng\Nnrestapi\Routing\Enhancer\NnrestapiEnhancer::ENHANCER_NAME] = \Nng\Nnrestapi\Routing\Enhancer\NnrestapiEnhancer::class;
	},
'nnrestapi');
