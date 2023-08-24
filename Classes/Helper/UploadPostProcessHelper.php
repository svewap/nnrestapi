<?php

namespace Nng\Nnrestapi\Helper;

use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Core\Imaging\GraphicalFunctions;

/**
 * ## UploadPostProcessHelper.
 * 
 */
class UploadPostProcessHelper
{
	/**
	 * 
	 * @param string $filename
	 * @param string $targetPath
	 * @param array $processingConfig
	 * @param UploadedFile $file
	 */
	public static function randomizeFilename( &$targetFileName = '', $targetPath = '', $processingConfig = [], $fileObj = null )
	{
		$absTargetPath = \nn\t3::File()->absPath( $targetPath );
		$filepathInStorage = ltrim( $targetFileName, $absTargetPath );

		$suffix = \nn\t3::File()->suffix($targetFileName);
		$newFilename = hrtime( true ) . md5($targetPath . $targetFileName) . ".{$suffix}";

		rename( $targetFileName, $absTargetPath . $newFilename );
		$targetFileName = $absTargetPath . $newFilename;
	}
	
	/**
	 * 
	 * @param string $filename
	 * @param string $targetPath
	 * @param array $processingConfig
	 * @param UploadedFile $file
	 */
	public static function imageMaxWidth( &$targetFileName = '', $targetPath = '', $processingConfig = [], $fileObj = null )
	{
		if (!\nn\t3::File()->isImage( $targetFileName )) {
			return;
		}

		$absTargetPath = \nn\t3::File()->absPath( $targetPath );
		$filepathInStorage = ltrim( $targetFileName, $absTargetPath );
		
		$maxWidth = $processingConfig['maxWidth'];
		$suffix = $processingConfig['filetype'] ?? \nn\t3::File()->suffix( $targetFileName );

		$outputPath = \nn\t3::File()->addSuffix( $absTargetPath . 'p' . $filepathInStorage, $suffix );

		try {
			$image = new \Imagick($targetFileName);
			$image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);		
			$image->stripImage();
		
			$width = $image->getImageWidth();
			$height = $image->getImageHeight();
		
			if ($width > $maxWidth) {
				$ratio = $height / $width;
				$newHeight = $maxWidth * $ratio;
				$image->resizeImage($maxWidth, $newHeight, \Imagick::FILTER_LANCZOS, 1);
			}
		
			$image->setImageFormat($suffix);
			if ($suffix == 'jpg') {
				$compressionQuality = $processingConfig['quality'] ?? 80;
				$image->setImageCompression(\Imagick::COMPRESSION_JPEG);
				$image->setImageCompressionQuality($compressionQuality);
			}

			$image->writeImage($outputPath);
		
			$image->clear();
			$image->destroy();
			
			unlink( $targetFileName );
			$targetFileName = $outputPath;

		} catch (\ImagickException $e) {
			\nn\rest::ApiError('nnrestapi postProcess image failed', 500, 500800, true);
		} catch (\Error $e) {
			\nn\rest::ApiError('nnrestapi postProcess image failed', 500, 500800, true);
		} catch (\Exception $e) {
			\nn\rest::ApiError('nnrestapi postProcess image failed', 500, 500800, true);
		}

	}
}