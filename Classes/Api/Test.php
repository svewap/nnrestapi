<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Nng\Nncalendar\Domain\Model\Entry;
use Nng\Nncalendar\Domain\Repository\EntryRepository;

/**
 * Nnrestapi
 * 
 */
class Test extends AbstractApi {
	
	/**
	 * Einfacher Test
	 * 
	 * GET test/
	 * 
	 * @access public
	 * @return array
	 */
	public function getIndexAction( $params = [], $payload = null )
	{
		\nn\t3::debug( $this ); die();
		$result = ['OK'=>123];
		return $result;
	}

	/**
	 * POST test/
	 * 
	 * @access public
	 * @return array
	 */
	public function postIndexAction( Entry $entry = null )
	{
		// \nn\t3::debug($entry);
		// \nn\t3::debug($this->request->getBody());
		// $result = ['OK'=>123];
		/*
		$repo = \nn\t3::injectClass( EntryRepository::class );
		$repo->add( $entry );
		\nn\t3::Db()->persistAll();
		*/
		return $entry;
	}

	

}
