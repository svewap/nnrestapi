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
	public static function Api() {
		return \nn\t3::injectClass(Api::class);
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