<?php 

namespace Nng\Nnrestapi\Utilities;

/**
 * Helper zum Bearbeiten von Dateien und Uploads
 * 
 */
class File extends \Nng\Nnhelpers\Singleton {

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
	 * @var array
	 */
	protected $uploadedFiles = [];
	
	/**
	 * Einstellungen für Dateiupload
	 * @var array
	 */
	protected $uploadConfig = [];

	/**
	 * Prüft das Request-JSON rekursiv nach `UPLOAD:/file-x` Einträgen.
	 * Kopiert Upload-Dateien ins Zielverzeichnis und ersetzt den `UPLOAD:/`-Pfad durch
	 * den "echten" Pfad im fileadmin, z.B. `fileadmin/uploads/bild.jpg`
	 * ```
	 * \nn\rest::File()->processFileUploadsInRequest();
	 * ```
	 * @return self
	 */
	public function processFileUploadsInRequest( $request ) {

		$body = $request->getBody();
		if (!$body || !is_array($body)) return;
		
		$this->uploadedFiles = $request->getUploadedFiles();
		$settings = $request->getSettings();

		$configKey = $request->getEndpoint()['uploadConfig'] ?? 'default';
		$this->uploadConfig = $settings['fileUploads'][$configKey] ?? false;

		// Klasse angegeben? Dann muss die Methode die Konfiguration zurückgeben.
		if ($pathHelper = $this->uploadConfig['pathFinderClass'] ?? false) {
			$this->uploadConfig = call_user_func( explode('::', $pathHelper), $request, $settings );
			if (!$this->uploadConfig) {
				\nn\t3::Exception("The method ${$pathHelper} for determining the fileUploadPath must return a valid configuration array.");
			}
		}

		if (!$this->uploadConfig) {
			\nn\t3::Exception(
				"Oups, no file-upload configuration found. 
				Either the default TypoScript-setup of EXT:nnrestapi was 
				not included – or you are missing a configuration at 
				`plugin.tx_nnrestapi.settings.fileUploads.{$configKey}`
			");
		}

		$this->processFileUploadsInRequestRecursive( $body );

		$request->setBody( $body );
		return $this;
	}

	/**
	 * Rekursive Methode zu `\nn\rest::File()->processFileUploadsInRequest()`.
	 * 
	 * @return void
	 */
	private function processFileUploadsInRequestRecursive( &$arr = [] ) {

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

			if ($processedFile = $this->processFileUpload( $v, $this->uploadedFiles, $configuration )) {
				$arr[$k] = $processedFile;
			} else {
				unset( $arr[$k] );
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
	public function processFileUpload( $placeholder = '', $uploadedFiles = [], $configuration = [] ) {
		
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
				\nn\t3::Exception("target-folder `{$targetPath}` doesn't exist and could not be created.");
			}

			// Kritische Datei-Typen?
			$srcFileName = pathinfo( $fileObj->getClientFilename(), PATHINFO_BASENAME);
			if (\nn\t3::File()->isForbidden($srcFileName)) {
				\nn\t3::Exception("Upload of filetype for `{$srcFileName}` not allowed!");
			}

			// Datei bereits verschoben? Dann vorhandene Datei nehmen.
			if ($existingFile = $this->movedFileUploads[$fileKey] ?? false) {
				$targetFileName = $existingFile;
			} else {
				$targetFileName = \nn\t3::File()->moveUploadedFile( $fileObj, $targetPath . $srcFileName );
				$targetFileName = \nn\t3::File()->stripPathSite( $targetFileName );
				$this->movedFileUploads[$fileKey] = $targetFileName;
			}

			return $targetFileName;
		}

		return $placeholder;
	}

}