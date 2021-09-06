<?php

namespace nn;

use Nng\Nnrestapi\Utilities\Api;
use Nng\Nnrestapi\Utilities\Endpoint;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class rest {

	/**
	 * Zugriff auf Utilities vereinfachen.
	 * Siehe `Classes/Utilities/Api` für alle Methoden.
	 * ```
	 * \nn\rest::Api()->methodName();
	 * ```
	 * @return Api
	 */
	public static function Api( $slug = null ) {
		if (!$slug) return \nn\t3::injectClass(Api::class);
		return new \Nng\Nnrestapi\Utilities\Api( $slug );
	}

	/**
	 * ```
	 * \nn\rest::Endpoint()->methodName();
	 * ```
	 * @return Endpoint
	 */
	public static function Endpoint() {
		return \nn\t3::injectClass(Endpoint::class);		
	}

}