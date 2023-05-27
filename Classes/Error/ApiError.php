<?php

namespace Nng\Nnrestapi\Error;

/**
 * Custom error that can be thrown to pass an additional
 * error-code and JSON.
 * 
 */
class ApiError extends \Error 
{
    /**
     * @var int|string $customErrorCode
     */
    private $customErrorCode = 200;

    public function __construct( $message, $errorCode = 400, $customErrorCode = 0 ) 
    {
        parent::__construct( $message, $errorCode );
        $this->customErrorCode = $customErrorCode;
    }

	/**
	 * @return  int|string
	 */
	public function getCustomErrorCode() {
		return $this->customErrorCode;
	}

	/**
	 * @param   int|string  $customErrorCode  $customErrorCode
	 * @return  self
	 */
	public function setCustomErrorCode($customErrorCode) {
		$this->customErrorCode = $customErrorCode;
		return $this;
	}

	/**
	 * @return array
	 */
	public function toArray() 
	{
		return [
			'code' 				=> $this->getCode(),
			'message' 			=> $this->getMessage(),
			'customErrorCode' 	=> $this->getCustomErrorCode(),
			'file' 				=> $this->getFile(),
			'line' 				=> $this->getLine(),
		];
	}
}