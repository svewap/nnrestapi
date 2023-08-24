<?php

namespace Nng\Nnrestapi\Helper;

use TYPO3\CMS\Core\Http\UploadedFile;

/**
 * ## AbstractUploadEncryptHelper.
 * 
 * All UploadEncryptHelpers should extend this class
 * @see UploadEncryptHelper for more information
 * 
 */
abstract class AbstractUploadEncryptHelper 
{
	/**
	 * @var array
	 */
	public $configuration = [];

    /**
     * The encryption key.
     *
     * @var string
     */
    protected $key;

    /**
     * The algorithm used for encryption.
     *
     * @var string
     */
    protected $cipher;

	/**
	 * @return void
	 */
	public function __construct( $config = [] ) 
	{
		$this->configuration = $config;
	}

	/**
	 * @param string $filename
	 * @param string $targetPath
	 * @param UploadedFile $file
	 * @return string
	 */
	public function getFilename( $filename = '', $targetPath = '', $file = null ) 
	{
		return $filename;
	}
	
	/**
	 * @param string $filename
	 * @param string $targetPath
	 * @param UploadedFile $file
	 * @return void
	 */
	public function encrypt( $filename = '', $targetPath = '', $fileObj = null ) {}

	/**
	 * @param string $file
	 * @return void
	 */
	public function decrypt( $sourcePath = '', $destPath = '' ) {}

	/**
	 * Open a file for writing
	 * @return mixed
	 */
	public function openDestFile($destPath)
    {
        if (($fpOut = fopen($destPath, 'w')) === false) {
			\nn\rest::ApiError('Cannot open file for writing.', 500);
        }

        return $fpOut;
    }

	/**
	 * Open a file for reading
	 * 
	 * @return mixed
	 */
    public function openSourceFile($sourcePath)
    {
        $contextOpts = self::startsWith($sourcePath, 's3://') ? ['s3' => ['seekable' => true]] : [];

        if (($fpIn = fopen($sourcePath, 'r', false, stream_context_create($contextOpts))) === false) {
			\nn\rest::ApiError('Cannot open file for reading.', 500);
        }

        return $fpIn;
    }

	/**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|iterable<string>  $needles
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        if (!is_iterable($needles)) {
            $needles = [$needles];
        }

        foreach ($needles as $needle) {
            if ((string) $needle !== '' && str_starts_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

}