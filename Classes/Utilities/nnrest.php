<?php

namespace nn;

use Nng\Nnrestapi\Utilities\Access;
use Nng\Nnrestapi\Utilities\Api;
use Nng\Nnrestapi\Utilities\Auth;
use Nng\Nnrestapi\Utilities\Annotations;
use Nng\Nnrestapi\Utilities\Endpoint;
use Nng\Nnrestapi\Utilities\Environment;
use Nng\Nnrestapi\Utilities\File;
use Nng\Nnrestapi\Utilities\Header;
use Nng\Nnrestapi\Utilities\Kickstart;
use Nng\Nnrestapi\Utilities\Query;
use Nng\Nnrestapi\Utilities\Settings;
use Nng\Nnrestapi\Utilities\Session;
use Nng\Nnrestapi\Utilities\Security;
use Nng\Nnrestapi\Utilities\Validator;

use Nng\Nnrestapi\Error\ApiError;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class rest {
	
	/**
	 * ```
	 * \nn\rest::Access()->methodName();
	 * ```
	 * @return Access
	 */
	public static function Access() {
		return \nn\t3::injectClass(Access::class);		
	}
	
	/**
	 * ```
	 * \nn\rest::Auth()->methodName();
	 * ```
	 * @return Auth
	 */
	public static function Auth() {
		return \nn\t3::injectClass(Auth::class);		
	}

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
	 * \nn\rest::Annotations()->methodName();
	 * ```
	 * @return Annotations
	 */
	public static function Annotations() {
		return \nn\t3::injectClass(Annotations::class);		
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
	
	/**
	 * ```
	 * \nn\rest::Environment()->methodName();
	 * ```
	 * @return Environment
	 */
	public static function Environment() {
		return \nn\t3::injectClass(Environment::class);		
	}
	
	/**
	 * ```
	 * \nn\rest::File()->methodName();
	 * ```
	 * @return File
	 */
	public static function File() {
		return \nn\t3::injectClass(File::class);		
	}
	
	/**
	 * ```
	 * \nn\rest::Header()->methodName();
	 * ```
	 * @return Header
	 */
	public static function Header() {
		return \nn\t3::injectClass(Header::class);		
	}

	/**
	 * ```
	 * \nn\rest::Kickstart()->methodName();
	 * ```
	 * @return Kickstart
	 */
	public static function Kickstart() {
		return \nn\t3::injectClass(Kickstart::class);		
	}

	/**
	 * ```
	 * \nn\rest::Query()->methodName();
	 * ```
	 * @return Query
	 */
	public static function Query( $className ) {
		return new Query( $className );		
	}

	/**
	 * ```
	 * \nn\rest::Settings( $request )->methodName();
	 * ```
	 * @return Settings
	 */
	public static function Settings( $request = null ) {
		return Settings::makeInstance( $request );
	}
	
	/**
	 * ```
	 * \nn\rest::Session( $request )->methodName();
	 * ```
	 * @return Session
	 */
	public static function Session( $request = null ) {
		return Session::makeInstance( $request );
	}
	
	/**
	 * ```
	 * \nn\rest::Security( $request )->methodName();
	 * ```
	 * @return Security
	 */
	public static function Security( $request = null ) {
		return Security::makeInstance( $request );
	}

	/**
	 * ```
	 * \nn\rest::Validator()->methodName();
	 * ```
	 * @return Validator
	 */
	public static function Validator() {
		return Validator::makeInstance();
	}

	/**
	 * Throws an error that will be returned as JSON
	 * ```
	 * \nn\rest::Error( 'Damn.' );
	 * \nn\rest::Error( 'Damn.', 403, 403123 );
	 * ```
	 * @param string $text     		Error message
	 * @param string $statusCode    http status code
	 * @param string $errorCode     custom code for JSON response
	 * @return void
	 */
	public static function ApiError( $message = '', $statusCode = 0, $customErrorCode = '' ) {
		throw new ApiError( $message, $statusCode, $customErrorCode );
	 }
}