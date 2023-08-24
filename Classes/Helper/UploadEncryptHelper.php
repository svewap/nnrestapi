<?php

namespace Nng\Nnrestapi\Helper;

use TYPO3\CMS\Core\Http\UploadedFile;

/**
 * ## UploadEncryptHelper.
 * 
 * Default (and example) helpers for encrypting files after upload.
 * 
 * You can use the Helpers by setting `pathFinderClass` in the TypoScript Setup:
 * ```
 * plugin.tx_nnrestapi.settings.fileUploadEncrypt.default {
 * 	encryptionClass = Nng\Nnrestapi\Helper\UploadEncryptHelper
 * }
 * ```
 * Make sure your endpoint uses the `@Api\Upload\Encrypt ...` annotation in case you are
 * using a different key than `default`.
 * 
 * 
 */
class UploadEncryptHelper extends AbstractUploadEncryptHelper
{
	/**
     * The encryption key.
     *
     * @var string
     */
    protected $key;
	
	/**
     * The cipher to use.
     * Can be overriden in TypoScript.
	 * 
     * @var string
     */
    protected $cipher = 'AES-128-CBC';
	
	/**
     * Number of bytes to read during stream
	 * 
     * @var string
     */
    protected $fileEncryptionBlocks = 255;

	/**
	 * Constructor
	 * @return void
	 */
	public function __construct( $config = [] ) 
	{
		parent::__construct( $config );

		$this->cipher = $config['cipher'] ?? 'AES-128-CBC';
		$this->fileEncryptionBlocks = $config['fileEncryptionBlocks'] ?? 255;

		$key = \nn\t3::Settings()->getExtConf('nnrestapi')['fileEncryptionKey'] ?? false;
		$cipherLength = $this->cipher == 'AES-128-CBC' ? 16 : 32;

		if (!$key) {
			$key = base64_encode(openssl_random_pseudo_bytes( $cipherLength ));
			\nn\t3::Settings()->setExtConf('nnrestapi', 'fileEncryptionKey', $key );
		}

		$key = base64_decode($key);
		if (!self::supported( $key, $this->cipher )) {
			\nn\rest::ApiError("
				The fileEncryptionKey defined for EXT:nnrestapi for cipher {$this->cipher} 
				must have a length of {$cipherLength}. Please check the configuration in the 
				Extension Manager. You can also delete the key to auto-generate a new key.", 500);
		}

		$this->key = $key;
	}

	/**
	 * Rename file before copying it from the `/tmp`-folder to 
	 * the destination. Must return a name with a `.enc` before
	 * the real suffix, e.g. `filename.enc.jpg`
	 * 
	 * @see \Nng\Nnhelpers\Utilities\File::$TYPES
	 * 
	 * @param string $filename
	 * @param string $targetPath
	 * @param UploadedFile $file
	 * @return string
	 */
	public function getFilename( $filename = '', $targetPath = '', $file = null ) 
	{
		$suffix = \nn\t3::File()->suffix($filename);
		return hrtime( true ) . md5($targetPath . $filename) . ".enc.{$suffix}";
	}

	/**
	 * Encrypt a file
	 * 
	 * @param string $filename
	 * @param string $targetPath
	 * @param UploadedFile $file	 
	 * @return void
	 */
	public function encrypt( $filename = '', $targetPath = '', $file = null ) 
	{
		$tmpFilename = $filename . '.tmp';
		$fpOut = $this->openDestFile( $tmpFilename );
        $fpIn = $this->openSourceFile( $filename );

        // Put the initialzation vector to the beginning of the file
        $iv = openssl_random_pseudo_bytes(16);
        fwrite($fpOut, $iv);

        $numberOfChunks = ceil(filesize($sourcePath) / (16 * $this->fileEncryptionBlocks));

        $i = 0;
        while (! feof($fpIn)) {
            $plaintext = fread($fpIn, 16 * $this->fileEncryptionBlocks);
            $ciphertext = openssl_encrypt($plaintext, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv);

            // Because Amazon S3 will randomly return smaller sized chunks:
            // Check if the size read from the stream is different than the requested chunk size
            // In this scenario, request the chunk again, unless this is the last chunk
            if (strlen($plaintext) !== 16 * $this->fileEncryptionBlocks
                && $i + 1 < $numberOfChunks
            ) {
                fseek($fpIn, 16 * $this->fileEncryptionBlocks * $i);
                continue;
            }

            // Use the first 16 bytes of the ciphertext as the next initialization vector
            $iv = substr($ciphertext, 0, 16);
            fwrite($fpOut, $ciphertext);

            $i++;
        }

        fclose($fpIn);
        fclose($fpOut);

		unlink($filename);
		rename($tmpFilename, $filename);

        return true;
	}

	/**
	 * Decrypt the file
	 * 
	 * @param string $sourcePath
	 * @param string $destPath
	 * @return void
	 */
	public function decrypt( $sourcePath = null, $destPath = '' ) 
	{
		$fpOut = $this->openDestFile($destPath);
        $fpIn = $this->openSourceFile($sourcePath);

        // Get the initialzation vector from the beginning of the file
        $iv = fread($fpIn, 16);

        $numberOfChunks = ceil((filesize($sourcePath) - 16) / (16 * (self::FILE_ENCRYPTION_BLOCKS + 1)));

        $i = 0;
        while (! feof($fpIn)) {
            // We have to read one block more for decrypting than for encrypting because of the initialization vector
            $ciphertext = fread($fpIn, 16 * (self::FILE_ENCRYPTION_BLOCKS + 1));
            $plaintext = openssl_decrypt($ciphertext, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv);

            // Because Amazon S3 will randomly return smaller sized chunks:
            // Check if the size read from the stream is different than the requested chunk size
            // In this scenario, request the chunk again, unless this is the last chunk
            if (strlen($ciphertext) !== 16 * (self::FILE_ENCRYPTION_BLOCKS + 1)
                && $i + 1 < $numberOfChunks
            ) {
                fseek($fpIn, 16 + 16 * (self::FILE_ENCRYPTION_BLOCKS + 1) * $i);
                continue;
            }

            if ($plaintext === false) {
				\nn\rest::ApiError('Decryption failed.', 500);
            }

            // Get the the first 16 bytes of the ciphertext as the next initialization vector
            $iv = substr($ciphertext, 0, 16);
            fwrite($fpOut, $plaintext);

            $i++;
        }

        fclose($fpIn);
        fclose($fpOut);

        return true;
	}


	/**
     * Determine if the given key and cipher combination is valid.
     *
     * @param  string  $key
     * @param  string  $cipher
     * @return bool
     */
    public static function supported($key, $cipher)
    {
        $length = mb_strlen($key, '8bit');

        return ($cipher === 'AES-128-CBC' && $length === 16) ||
               ($cipher === 'AES-256-CBC' && $length === 32);
    }
}