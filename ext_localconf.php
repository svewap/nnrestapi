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

		// Custom HTTP handler to process PUT / PATCH / DELETE Requests. As all ext_localconf.php are included during 
		// TYPO3 bootstrap BEFORE processing the request, this script will be executed prior to anything else
		\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Nng\Nnrestapi\Middleware\RequestParser::class)->handler();
		
		// Nur für Typo3 < 11 erforderlich
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers'][\Nng\Nnrestapi\Routing\Enhancer\NnrestapiEnhancer::ENHANCER_NAME] = \Nng\Nnrestapi\Routing\Enhancer\NnrestapiEnhancer::class;
	
		// Needed to override HiddenRestrictions when retrieving hidden records in Frontend Context
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Extbase\Persistence\Generic\QueryFactory::class] = [
			'className' => \Nng\Nnrestapi\Xclass\QueryFactory::class
		];
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction::class] = [
			'className' => \Nng\Nnrestapi\Xclass\HiddenRestriction::class
		];

		// Hook in `/sysext/core/Classes/Authentication/AbstractUserAuthentication.php` to auth the frontend-user
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']['nnrestapi'] = \Nng\Nnrestapi\Hooks\FrontendUserAuthenticationHook::class . '->postUserLookUp';
	},
'nnrestapi');
