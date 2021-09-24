<?php

namespace Nng\Nnrestapi\Helper;

/**
 * ## UploadPathHelper.
 * 
 * Default (and example) helpers for calculating the path of uploadFiles while
 * they are moved from the `/tmp` folder to the target-folder. The path can follow
 * any logic that you need. It must return a configuration-array.
 * 
 * You can use the Helpers by setting `pathFinderClass` in the TypoScript Setup:
 * ```
 * plugin.tx_nnrestapi.settings.fileUploads.default {
 * 	defaultStoragePath = 1:/
 * 	pathFinderClass = Nng\Nnrestapi\Helper\UploadPathHelper::getPathForDate
 * }
 * ```
 * Make sure your endpoint uses the `@api\upload ...` annotation in case you are
 * using a different key than `default`.
 * 
 * The Helper will return a configuration array which has the same keys and structure
 * that the TypoScript setup uses. You can keep things simple and just return the
 * key `defaultStoragePath` which will upload all fileUploads to the same location, 
 * independant of their fileKey/name in the POST-data:
 * ```
 * return ['defaultStoragePath'=>'1:/my/custom/path']
 * ```
 * And/or you can return a path for the individual fileKeys:
 * ```
 * return ['file'=>'1:/files/', 'image-0':'1:/images/', ...];
 * ```
 * 
 */
class UploadPathHelper {

	/**
	 * Structure uploads in folders named by `YYYY-MM` - similar to the way
	 * WordPress would store files. All files are in the same folder here.
	 * 
	 * ```
	 * pathFinderClass = Nng\Nnrestapi\Helper\UploadPathHelper::getPathForDate
	 * ```
	 * @return array
	 */
	public static function getPathForDate( $request = null, $settings = null ) {

		$storage = $settings['fileUploads']['default']['defaultStoragePath'] ?? '1:/';

		return [
			'defaultStoragePath' => "{$storage}" . date('Y-m') . '/'
		];
	}

	/**
	 * Structure uploads in folders with the uid of the `fe_user`.
	 * User `1` will upload to `fileadmin/api/1/...` etc.
	 * 
	 * ```
	 * pathFinderClass = Nng\Nnrestapi\Helper\UploadPathHelper::getUserUidPath
	 * ```
	 * @return array
	 */
	public static function getUserUidPath( $request = null, $settings = null ) {

		$storage = $settings['fileUploads']['default']['defaultStoragePath'] ?? '1:/';
		$feUserUid = $request->getFeUser()['uid'] ?? 'unknown';

		return [
			'defaultStoragePath' => "{$storage}" . $feUserUid . '/'
		];
	}
}