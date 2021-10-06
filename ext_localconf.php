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

		// AUTH-Service Registrierung. Delegiert an alle Auth-Services, die mit `\nn\rest::Auth()->register()` registiert wurden
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
			$extKey,
			'auth',
			\Nng\Nnrestapi\Service\AuthenticationService::class,
			[
				'title' => 'Authentification service (nnrestapi)',
				'description' => 'Authentication service for login of RestApi.',
				'subtype' => 'getUserFE,authUserFE,getGroupsFE',
				'available' => true,
				'priority' => 80,
				'quality' => 80,
				'os' => '',
				'exec' => '',
				'className' => \Nng\Nnrestapi\Service\AuthenticationService::class,
			]
		);

	},
'nnrestapi');
