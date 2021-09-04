<?php
namespace Nng\Nnrestapi\Service;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Nnrestapi
 * 
 */
class AutoloadService
{

	/**
	 * 	composer-Libraries laden
     * 	\Nng\Nnrestapi\Service\AutoloadService::loadLibraries();
	 * 
	 * 	@return void
	 */
	public static function loadLibraries () {
		require_once( \nn\t3::Environment()->extPath('nnrestapi') . 'Resources/Libraries/vendor/autoload.php');
	}
	
}
