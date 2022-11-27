<?php
defined('TYPO3') or die();

call_user_func(
	function( $extKey )
	{
		
		// Allow table
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_nnrestapi_domain_model_apitest');

    },
'nnrestapi');
