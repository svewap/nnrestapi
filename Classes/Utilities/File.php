<?php 

namespace Nng\Nnrestapi\Utilities;

use Nng\Nnrestapi\Helper\AbstractUploadEncryptHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * Helper zum Bearbeiten von Dateien und Uploads
 * 
 */
class File extends \Nng\Nnhelpers\Singleton 
{	
	/**
	 * Prefix im JSON, der für einen Dateiupload verwendet wird.
	 * @var string
	 */
	protected $uploadPrefix = 'UPLOAD:/';

	/**
	 * Cache für Dateien, die bereits kopiert wurden
	 * @var string
	 */
	protected $movedFileUploads = [];
	
	/**
	 * Dateien, die im POST-Container des Requests hochgeladen wurden
	 * @var array<\TYPO3\CMS\Core\Http\UploadedFile>
	 */
	protected $uploadedFiles = [];
	
	/**
	 * Uploads als TYPO3 SysFiles
	 * @var array<\TYPO3\CMS\Core\Resource\File>
	 */
	protected $uploadedSysFiles = [];
	
	/**
	 * Einstellungen für Dateiupload
	 * @var array
	 */
	protected $uploadConfig = [];
	
	/**
	 * Settings for file-encryption
	 * @var array
	 */
	protected $uploadEncryptConfig = [];

	/**
	 * Instance of the encryption class
	 * @var AbstractUploadEncryptHelper
	 */
	protected $uploadEncryptionClass;

	/**
	 * Prüft das Request-JSON rekursiv nach `UPLOAD:/file-x` Einträgen.
	 * Kopiert Upload-Dateien ins Zielverzeichnis und ersetzt den `UPLOAD:/`-Pfad durch
	 * den "echten" Pfad im fileadmin, z.B. `fileadmin/uploads/bild.jpg`
	 * ```
	 * \nn\rest::File()->processFileUploadsInRequest();
	 * ```
	 * @return self
	 */
	public function processFileUploadsInRequest( $request ) 
	{
		$body = $request->getBody();
		if (!$body || !is_array($body)) return;

		$this->uploadedFiles = $request->getUploadedFiles();
		$settings = $request->getSettings();

		$configKey = $request->getEndpoint()['uploadConfig'] ?? false;

		// config-key set to FALSE? `@Api\Upload(FALSE)` - deny ANY fileupload!
		if ($configKey === false) {
			$this->uploadConfig = false;
			$this->processFileUploadsInRequestRecursive( $body );
			$request->setBody( $body );
			return $this;
		}

		// config-key passed? `@Api\Upload("config[name]")`
		if (preg_match('/config\[(.*)\]/', $configKey, $matches)) {
			$configKey = $matches[1];
		}

		$this->uploadConfig = $settings['fileUploads'][$configKey] ?? [];

		// combined identifier passed? `@Api\Upload("1:/path/to/somewhere/")`
		if (preg_match('/[0-9]+:\/(.*)/', $configKey)) {
			$this->uploadConfig['defaultStoragePath'] = $configKey;
		}

		// classname passed? `@Api\Upload( \My\Extname\UploadProcessor::class )`
		if ( class_exists( $configKey ) ) {
			$this->uploadConfig['pathFinderClass'] = $configKey . '::getUploadPath';
		}

		// Klasse angegeben? Dann muss die Methode die Konfiguration zurückgeben.
		if ($pathHelper = $this->uploadConfig['pathFinderClass'] ?? false) {
			$this->uploadConfig = call_user_func( explode('::', $pathHelper), $request, $settings );
			if (!$this->uploadConfig) {
				\nn\rest::ApiError("The method ${$pathHelper} for determining the fileUploadPath must return a valid configuration array.");
			}
		}

		if (!$this->uploadConfig) {
			\nn\rest::ApiError(
				"Oups, no file-upload configuration found. 
				Either the default TypoScript-setup of EXT:nnrestapi was 
				not included – or you are missing a configuration at 
				`plugin.tx_nnrestapi.settings.fileUploads.{$configKey}`
			");
		}

		// get settings for encryption (can be set in `@Api\Upload\Encrypt("config[name]")`)
		$configKey = $request->getEndpoint()['uploadEncryptConfig'] ?? false;

		if (preg_match('/config\[(.*)\]/', $configKey, $matches)) {
			$configKey = $matches[1];
		}

		// (!!!) EXPERIMENTAL. get configuration and class to encrypt / decrypt the data from TypoScript
		$this->uploadEncryptConfig = $settings['fileUploadEncrypt'][$configKey] ?? [];

		if ($class = $this->uploadEncryptConfig['encryptionClass'] ?? false) {
			$this->uploadEncryptionClass = new $class($this->uploadEncryptConfig);
			if (!$this->uploadEncryptionClass) {
				\nn\rest::ApiError("Oups, class for Encryption not found! Looked for: {$class}");
			}
		}

		// start processing the file uploads
		$this->processFileUploadsInRequestRecursive( $body );

		$request->setBody( $body );
		$request->setUploadedSysFiles( $this->uploadedSysFiles );
		return $this;
	}

	/**
	 * Rekursive Methode zu `\nn\rest::File()->processFileUploadsInRequest()`.
	 * 
	 * @return void
	 */
	private function processFileUploadsInRequestRecursive( &$arr = [] ) 
	{
		$configuration = $this->uploadConfig;

		foreach ($arr as $k=>$v) {

			// Array? 
			if (is_array($v)) {

				// enthält das Array ein `['publicUrl'=>'UPLOAD:/file-0']`?
				if ($publicUrl = $v['publicUrl'] ?? false) {
					if ($processedFile = $this->processFileUpload( $publicUrl, $this->uploadedFiles, $configuration )) {
						$arr[$k]['publicUrl'] = $processedFile;
					} else {
						unset( $arr[$k] );
					}
					continue;
				} else {
					$this->processFileUploadsInRequestRecursive( $arr[$k] );
					continue;
				}
			}

			if (strpos($v, $this->uploadPrefix) === 0) {
				if ($processedFile = $this->processFileUpload( $v, $this->uploadedFiles, $configuration )) {
					$arr[$k] = $processedFile;
				} else {
					unset( $arr[$k] );
				}
			}
		}
	}

	/**
	 * Upload-Datei aus dem `/tmp`-Verzeichnis ins Zielverzeichnis kopieren.
	 * Temporären Dateipfad (`UPLOAD:/file-0`) durch den Pfad der kopierten
	 * Datei ersetzen (`fileadmin/myfolder/file.jpg`).
	 * 
	 * - Schlägt das Kopieren fehl, oder existiert die Datei nicht, wird `false` zurückgegeben
	 * - Wird das Prefix `UPLOAD:/` nicht gefunden, wird `$placeholder` unverändert zurückgegeben
	 * 
	 * In `$configuration` kann der Zielordner pauschal – oder pro File-Key angegeben werden.
	 * Falls keine `$configuration` angegeben wird, landen die Dateien im Ordner `fileadmin/`
	 * Falls für `$configuration` `FALSE` angegeben wird, werden alle Datei-Uploads entfernt.
	 *  
	 * ```
	 * // Dateien in die Default-Storage kopieren (fileadmin)
	 * \nn\rest::File()->processFileUpload( 'UPLOAD:/file-0', $uploadedFiles );
	 * 
	 * // Dateien in fileadmin/myfolder/ kopieren, mit und ohne combinedIdentifier-Schreibweise
	 * \nn\rest::File()->processFileUpload( 'UPLOAD:/file-0', $uploadedFiles, '1:/myfolder' ); 
	 * \nn\rest::File()->processFileUpload( 'UPLOAD:/file-0', $uploadedFiles, 'fileadmin/myfolder' );
	 * 
	 * // file, file-0, file-1 etc. in fileadmin/myfolder/ kopieren
	 * \nn\rest::File()->processFileUpload( 'UPLOAD:/file-0', $uploadedFiles, ['file'=>'1:/myfolder'] );
	 * 
	 * // file, file-0, file-1 etc. in fileadmin/myfolder/ kopieren. Alle anderen in fileadmin/
	 * \nn\rest::File()->processFileUpload( 'UPLOAD:/file-0', $uploadedFiles, ['defaultStoragePath'=>'1:/', 'file'=>'1:/myfolder'] );
	 * 
	 * // file-0 in fileadmin/myfolder/, file-1 in fileadmin/other/
	 * \nn\rest::File()->processFileUpload( 'UPLOAD:/file-0', $uploadedFiles, ['file-0'=>'1:/myfolder', 'file-1'=>'1:/other'] );
	 * ```
	 * 
	 * @return string|boolean
	 */
	public function processFileUpload( $placeholder = '', $uploadedFiles = [], $configuration = [] ) 
	{
		// `FALSE`? Simply ignore the file.
		if ($configuration === false) {
			return false;
		}

		// `UPLOAD:/...` als Prefix vorhanden?
		if (strpos($placeholder, $this->uploadPrefix) === 0) {

			// `UPLOAD:/file-0` ==> `file-0`
			$fileKey = str_replace($this->uploadPrefix, '', $placeholder);
			$fileObj = $uploadedFiles[$fileKey] ?? false;

			// Nicht als file-upload im POST-Request übergeben? Dann vergessen wir das einfach.
			if (!$fileObj || $fileObj->getError() || !$fileObj->getSize()) {
				return false;
			}

			if (is_array($configuration)) {
				// `file-0` => `file` -- `file-test-1` => `file-test`
				$baseFileKey = preg_replace('/(.*)\-([0-9]*)/i', '\1', $fileKey );
				$targetPath = $configuration[$fileKey] ?? $configuration[$baseFileKey] ?? $configuration['defaultStoragePath'] ?? '1:/';
			} else {
				$targetPath = $configuration ?: '1:/';
			}

			// Zielordner erstellen – falls nicht vorhanden
			if (!\nn\t3::File()->mkdir($targetPath)) {
				\nn\rest::ApiError("target-folder `{$targetPath}` doesn't exist and could not be created.");
			}

			// Kritische Datei-Typen?
			$srcFileName = pathinfo( $fileObj->getClientFilename(), PATHINFO_BASENAME);
			if (\nn\t3::File()->isForbidden($srcFileName)) {
				\nn\rest::ApiError("Upload of filetype for `{$srcFileName}` not allowed!", 403, true);
			}

			// Datei bereits verschoben? Dann vorhandene Datei nehmen.
			if ($existingFile = $this->movedFileUploads[$fileKey] ?? false) {
				$targetFileName = $existingFile;
			} else {

				// if a encryptionClass was defined: ask it to rename the file
				if ($this->uploadEncryptionClass) {
					$srcFileName = $this->uploadEncryptionClass->getFilename( $srcFileName, $targetPath, $fileObj );
				}

				$targetFileName = \nn\t3::File()->moveUploadedFile( $fileObj, $targetPath . $srcFileName );
				$targetFileName = \nn\t3::File()->stripPathSite( $targetFileName );

				// was post-processing defined?
				$postProcessing = $this->uploadConfig['postProcess'] ?? [];
				if ($postProcessing) {
					foreach ($postProcessing as $k=>$processingConfig) {
						$methodToCall = $processingConfig['userFunc'] ?? false;
						if (!$methodToCall) continue;
						call_user_func_array(
							explode('::', $methodToCall), 
							[&$targetFileName, $targetPath, $processingConfig, $fileObj] 
						);
					}
				}

				$this->movedFileUploads[$fileKey] = $targetFileName;

				// Encrypt files?
				if ($this->uploadEncryptionClass) {
					$this->uploadEncryptionClass->encrypt( $targetFileName, $targetPath, $fileObj );
				}

				$sysFile = \nn\t3::Fal()->getFalFile( $targetFileName );
				$this->uploadedSysFiles[$fileKey] = $sysFile;

			}

			return $targetFileName;
		}

\nn\t3::debug($this->uploadedSysFiles);
		return $placeholder;
	}

	/**
	 * Get all files, recursively in a directory
	 * 
	 * ```
	 * \nn\rest::File()->getAllInFolder( 'EXT:nnrestapi/path/to/files/' );
	 * ```
	 * @return array
	 */
	public function getAllInFolder( $path = '', $recursive = true, $suffix = false ) 
	{
		$files = [];
		$path = \nn\t3::File()->absPath( $path );
		
		if (!$path || !\nn\t3::File()->exists($path)) return [];

		$flags = \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator( $path, $flags ));

		foreach ($iterator as $fileObject) {
			if (!$fileObject->isDir()) {
				$pathName = $fileObject->getPathname();
				$extension = pathinfo($pathName, PATHINFO_EXTENSION);
				if (!$suffix || $extension == $suffix) {
					$files[] = $pathName;
				}
			}
		}
		
		return $files;
	}

	/**
	 * Get all files content, recursively in a directory
	 * Returns an array with the path to the file as key and the content as value.
	 * 
	 * @return array 
	 */
	public function getFolderContent( $path = '' ) 
	{
		$absPath = \nn\t3::File()->absPath( $path );
		$dirname = basename( $absPath );
		$files = $this->getAllInFolder( $path );
		$filesByRelPath = [];

		foreach ($files as $file) {
			$relPath = str_replace( $absPath, '', $file );
			$contents = file_get_contents( $file );
			$filesByRelPath[$dirname . '/' . $relPath] = $contents;
		}

		return $filesByRelPath;
	}

	/**
	 * Get complete content of ZIP-file as array.
	 * Returns an array with the path to the file as key and the content as value.
	 * The zip is not physically unpacked for this.
	 * 
	 * @return array
	 */
	public function getZipContent( $path = '' ) 
	{
		$absPath = \nn\t3::File()->absPath( $path );
		
		$archive = new \ZipArchive();
		$archive->open( $absPath );
		$files = [];

		for( $i = 0; $i < $archive->numFiles; $i++ ){ 
			$stat = $archive->statIndex( $i );
			$filename = $stat['name'];
			if (substr(basename($filename), 0, 2) == '._') {
				continue;
			}
			$files[$filename] = $archive->getFromIndex( $i ); 
		}

		return $files;
	}
}