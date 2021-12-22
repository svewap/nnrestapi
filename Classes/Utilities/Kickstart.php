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
	 * Export all files in folder recursively.
	 * Replaces markers.
	 * 
	 * ```
	 * \nn\rest::Kickstart()->createExtensionFromTemplate( $path, $filename, $marker );
	 * ```
	 * 
	 * @param string $path 			The path to the templates (e.g. `EXT:nnrestapi/Resources/...`) 
	 * @param string $filename 		The filename of the zip to download to the templates (e.g. `myext`) 
	 * @param array $marker 		The maker to replace (see above) 
	 * 
	 * @return void
	 */
	public function createExtensionFromTemplate( $path = '', $filename = '', $marker = [] ) {

		// get all files from given folder
		$files = \nn\rest::File()->getAllInFolder( $path );
		$absPath = \nn\t3::File()->absPath( $path );

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

		$archiveFilename = $filename ?: 'download-'.date('Y-m-d');		
		$stream = fopen('php://output', 'w');
		$opt = [];

		// gmp_init is required for a zip-Stream. Otherwise use the .tar version
		if (function_exists('gmp_init')) {
			$zipStream = \Barracuda\ArchiveStream\Archive::instance_by_useragent( $archiveFilename, $opt, $stream );
		} else {
			$zipStream = new \Barracuda\ArchiveStream\TarArchive( $archiveFilename.'.tar', $opt, null, $stream );
		}

		// now pack every file from template in zip-file
		foreach ($files as $k=>$file) {
			
			$filesize = \nn\t3::File()->size( $file );

			$relFilename = str_replace( $absPath, '', $file );
			$relFilename = str_replace( '.tmpl', '', $relFilename );
			$fileNameInArchive = $relFilename;

			$zipStream->init_file_stream_transfer( $fileNameInArchive, $filesize );

			// read file content and replace placeholder for vendor-name and extension-name
			$content = \nn\t3::File()->read( $file );
			$content = str_replace( array_keys($marker), array_values($marker), $content );

			$zipStream->stream_file_part( $content );
			$zipStream->complete_file_stream();
		}

		// send zip-file and abort script
		$zipStream->finish();

		die();
	}
	
}