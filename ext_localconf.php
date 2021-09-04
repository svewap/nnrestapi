<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
	function( $extKey )
	{
		\nn\t3::Registry()->configurePlugin( 'Nng\Nnrestapi', 'api', 
            [\Nng\Nnrestapi\Controller\MainController::class => 'index'],
            [\Nng\Nnrestapi\Controller\MainController::class => 'index']
        );
	},
'nnrestapi');
