<?php
namespace Nng\Nnrestapi\Service;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Nnrestapi
 * 
 */
class FrontendUserService
{

	/**
	 * Aktuellen FE-User holen inkl. Benutzergruppe
     * ```
	 * \Nng\Nnrestapi\Service\FrontendUserService::getFeUser();
	 * ```
	 * 	@return array
	 */
	public static function getFeUser () {
		$feUser = \nn\t3::FrontendUser()->getCurrentUser();
		$feUserGroup = \nn\t3::FrontendUser()->getCurrentUserGroups( true );
		$firstUserGroup = array_pop( $feUserGroup );

		$vars = [
			'sso'			=> \nn\t3::FrontendUser()->getSessionId(),
			'user'	 		=> \nn\t3::Obj()->props($feUser, ['uid', 'pid', 'username', 'name']),
			'privileges'	=> \nn\t3::Flexform()->parse( $firstUserGroup['nnrestapi_flexform'] ?? '' )
		];
		return $vars;
	}
	

	/**
	 * Fe-User aus uid auflösen im Feld "editstatus"
	 * ```
	 * \Nng\Nnrestapi\Service\FrontendUserService::resolveFeUsers( $list, $field );
	 * ```
	 * @return array
	 */
	public static function resolveFeUsers( $list, $field = 'editstatus' ) {

		if (!$list) return $list;
		$returnFlat = false;

		if ($list['uid'] ?? false) {
			$list = \nn\t3::Arrays([$list]);
			$returnFlat = true;
		}
		
		// Liste der Fe-User laden, die zur Zeit am Bearbeiten der Einträge sind
		$list = $list->toArray();
		$feUserList = array_column( $list, $field );
		$feUserList = \nn\t3::Arrays($feUserList)->trimExplode();

		if ($feUserList) {
			$queryBuilder = \nn\t3::Db()->getQueryBuilder( 'fe_users' );
			$queryBuilder->select('uid', 'username', 'first_name AS firstname', 'last_name AS lastname')->from( 'fe_users' );
			\nn\t3::Db()->ignoreEnableFields( $queryBuilder, true, true );
			$feUsers = $queryBuilder->andWhere( $queryBuilder->expr()->in('uid', $feUserList))->executeQuery()->fetchAllAssociative();
			$feUsersByUid = \nn\t3::Arrays($feUsers)->key('uid')->toArray();
		}

		foreach ($list as &$item) {
			$user = [];
			if ($feUserUid = $item[$field]) {
				if ($feUsersByUid[$feUserUid] ?? false) {
					$user = $feUsersByUid[$feUserUid];
				}
			}
			$item[$field] = $user;
		}
		return $returnFlat ? array_pop($list) : $list;
	}

}
