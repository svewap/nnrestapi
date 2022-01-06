<?php 

namespace Nng\Nnrestapi\Utilities;

/**
 * Helper for generating TYPO3-extensions from templates
 * 
 * Marker to use in Templates:
 * 
 * `[#ext-ucc#]`		extension-name, UpperCamelCase - e.g. `MyExt`
 * `[#ext-lower#]`		extension-name, lowercase - e.g. `myext`
 * `[#vendor-ucc#]`		vendor-name, UpperCamelCase - e.g. `Nng`
 * `[#vendor-lower#]`	vendor-name, lowercase - e.g. `nng`
 * 
 */
class Kickstart extends \Nng\Nnhelpers\Singleton 
{
	/**
	 * If set to `false`, the PHP ZipArchive will be used instead of external lib
	 * 
	 * @var boolean
	 */
	protected $useExternalLib = false;

	/**
	 * Read all files from a ZIP file, replaces markers in source code and 
	 * repack it to a ZIP (or TAR) that is downloaded.
	 * 
	 * ```
	 * \nn\rest::Kickstart()->createExtensionFromTemplate( $path, $filename, $marker );
	 * ```
	 * 
	 * @param string $path 			The path to the templates-Zip (e.g. `EXT:nnrestapi/Resources/.../demo.zip`) 
	 * @param string $filename 		The filename of the zip to download to the templates (e.g. `myext`) 
	 * @param array $marker 		The maker to replace (see above) 
	 * 
	 * @return void
	 */
	public function createExtensionFromTemplate( $config = [], $marker = [] ) {

		// extension-name
		$extname = $marker['[#ext-lower#]'];

		if (\nn\t3::File()->isFolder( $config['path'] )) {
			// get all files from given folder
			$files = \nn\rest::File()->getFolderContent( $config['path'] );
		} else {
			// get all files from given zip-file
			$files = \nn\rest::File()->getZipContent( $config['path'] );
		}

		if (!$files) return false;

		// nnhelpers autoload: We need the ZIP-library
		\nn\t3::autoload();

		// make sure, the server is not sending a compressed file.
		if ($GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'] ?? false) {
			header('Content-Encoding: none');
			if (function_exists('apache_setenv')) {
				apache_setenv('no-gzip', '1');
			}
			if (extension_loaded('zlib')) {
				@ini_set('zlib.output_compression', 'off');
				@ini_set('zlib.output_compression_level', '0');
			}
		}

		$archiveFilename = $extname ?: 'download-'.date('Y-m-d');	
		
		// use external lib?
		if ($this->useExternalLib) {

			$stream = fopen('php://output', 'w');
			$opt = [];
	
			// gmp_init is required for a zip-Stream. Otherwise use the .tar version
			if (function_exists('gmp_init')) {
				$zipStream = \Barracuda\ArchiveStream\Archive::instance_by_useragent( $archiveFilename, $opt, $stream );
			} else {
				$zipStream = new \Barracuda\ArchiveStream\TarArchive( $archiveFilename.'.tar', $opt, null, $stream );
			}
			
			// now pack every file back in a streamed zip-file
			foreach ($files as $fileNameInArchive=>$content) {
	
				// replace first path part with ext-name (`apitest/Controller/Test.php` => `foobar/Controller/Test.php`)
				$fileNameInArchive = $extname . '/' . substr($fileNameInArchive, strpos($fileNameInArchive, '/', 1));
	
				// replace placeholders in the filename
				$fileNameInArchive = strtr( $fileNameInArchive, $marker );
	
				// replace placeholder for vendor-name and extension-name in scripts
				$content = strtr( $content, $marker );
	
				$filesize = strlen( $content );
				$zipStream->init_file_stream_transfer( $fileNameInArchive, $filesize );
	
				$zipStream->stream_file_part( $content );
				$zipStream->complete_file_stream();
			}
	
			// send zip-file and abort script
			$zipStream->finish();
			die();
		}

		// use standard PHP ZipArchive
		$zip = new \ZipArchive();

		$archivePath = \nn\t3::Environment()->getVarPath() . $archiveFilename . '.zip';
		@unlink($archivePath);

		if (!$zip->open($archivePath, \ZipArchive::CREATE)) {
			\nn\t3::Exception( 'Could not create zip-file in ' . $archivePath );
		}

		// see above for comments
		foreach ($files as $fileNameInArchive=>$content) {
			$fileNameInArchive = $extname . '/' . substr($fileNameInArchive, strpos($fileNameInArchive, '/', 1));
			$fileNameInArchive = strtr( $fileNameInArchive, $marker );
			$content = strtr( $content, $marker );
			$zip->addFromString( $fileNameInArchive, $content );
		}

		$zip->close();
		\nn\t3::File()->sendDownloadHeader( $archivePath );
		echo \nn\t3::File()->read( $archivePath );

		@unlink($archivePath);
		die();
	}
	
}