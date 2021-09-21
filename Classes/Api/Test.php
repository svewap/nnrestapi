<?php
namespace Nng\Nnrestapi\Api;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Nng\Nncalendar\Domain\Model\Entry;
use Nng\Nncalendar\Domain\Repository\EntryRepository;

/**
 * Nnrestapi
 * 
 * Beispiele für Routing per @request:
 * ```
 * @request /test/demo
 * @request /test/demo/{uid} 
 * @request /test/demo/{uid?}
 * @request /test/demo/{uid}/{test}
 * @request /test/demo/{uid?}/{test?}
 * @request GET /test/demo/something
 * @request GET|POST|PUT /test/demo/something
 * ```
 */
class Test extends AbstractApi {
	
	/**
	 * Einfacher Test
	 * 
	 * @access fe_groups[2]
	 * @return array
	 */
	public function getIndexAction()
	{
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
