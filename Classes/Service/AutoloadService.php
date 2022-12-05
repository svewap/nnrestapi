<?php
namespace Nng\Nnrestapi\Service;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
	public static function loadLibraries () 
	{
		$path = GeneralUtility::getFileAbsFileName( 'EXT:nnrestapi/Resources/Libraries/vendor/autoload.php' );
		require_once( $path );
	}
	
}
