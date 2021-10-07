<?php 

namespace Nng\Nnrestapi\Utilities;

/**
 * Helper für die Authentifizierungen von Frontend-Usern.
 * 
 */
class Access extends \Nng\Nnhelpers\Singleton {

	/**
	 * Cache for user data, reduce the number of queries to the DB.
	 * 
	 * @var array
	 */
	private $userCache = [];


	/**
	 * Parses the `@Api\Access(...)` value which defines the access-rights to an endpoint.
	 * Returns a clean and beautified array with `fe_users`, `be_users` etc.
	 * 
	 * Any imaginable combination of strings and/or arrays is allowed.
	 * 
	 * ```
	 * \nn\rest::Access()->parse('config[apiUsers]');
	 * \nn\rest::Access()->parse('config[apiUsers],fe_users', 'be_users[1,2]');
	 * \nn\rest::Access()->parse('fe_groups[api_users,api_users_2]');
	 * \nn\rest::Access()->parse(['fe_users[david,tanja]', 'be_users[1]', 'public']);
	 * ```
	 * 
	 * @param mixed $accessStr
	 * @return array
	 */
	public function parse( $access = '' ) {

		if (is_array($access)) $access = join(',', $access);
		
		$regEx = '/([^\[,]*)(\[([^\]]*)\])?/i';

		preg_match_all($regEx, $access, $accessArr);
		$accessArr = \nn\t3::Arrays($accessArr[0] ?? [])->removeEmpty();

		// A simple `*` is an alias for `public`
		foreach ($accessArr as &$rule) {
			if ($rule == '*') $rule = 'public';
		}

		$accessStr = join(',', $accessArr );
		$accessList = [];

		// Definition der Gruppen aus der siteConfig-Yaml
		$groupConfigurations = \nn\rest::Settings()->getConfiguration('accessGroups') ?? [];

		if (preg_match_all($regEx, $accessStr, $matches)) {
			foreach ($matches[1] as $n=>$v) {
				$v = trim($v);
				if (!$v) continue;
				
				// Liste der uids oder usernamen in den Klammern, z.B. `fe_users[...]`
				$userList = \nn\t3::Arrays($matches[3][$n] ?? '')->trimExplode();
				
				// Keine Einschränkungen auf bestimmte uids oder usernamen bedeutet: ALLE dieses Typs dürfen!
				if (!count($userList)) {
					$userList = ['*'=>'*'];
				}

				// `config` nehmen aus der YAML site-Konfiguration
				if ($v == 'config') {
					foreach ($userList as $configKey) {
						$accessStrFromConfig = $groupConfigurations[$configKey] ?? false;
						if (!$accessStrFromConfig) continue;
						$parsedAccessList = $this->parse( $accessStrFromConfig );
						$accessList = \nn\t3::Arrays($accessList)->merge($parsedAccessList, true, true);
					}
					continue;
				}

				// `be_users` oder `fe_users`
				if (!isset($accessList[$v])) {
					$accessList[$v] = [];
				}

				$accessList[$v] = array_merge( $accessList[$v], $userList );
			}
		}

		$accessList = $this->convertUserNamesToUids( $accessList );

		return $accessList;
	}

	
	/**
	 * Resolve `username` in to its corresponding `uid` from the `fe_users`-table.
	 * 
	 * ```
	 * \nn\rest::Access()->convertUserNamesToUids();
	 * ```
	 * @return array
	 */
	public function convertUserNamesToUids( $accessList ) {

		// Diese Tabellen werden für die Konvertierung berücksichtigt
		$tables = ['fe_groups'=>'title', 'fe_users'=>'username', 'be_users'=>'username', 'public'=>''];

		foreach ($tables as $table=>$field) {
			if (!isset($accessList[$table])) continue;

			// Bereits ALLE User erlaubt? z.B. über `@access fe_users` ohne `[1,2,...]`
			$anyUserAllowed = $accessList[$table]['*'] ?? false;

			// Leeres Array bedeutet hier: In der `@access`-Annotation wurden ALLE User der Gruppe erlaubt
			if ($anyUserAllowed || count($accessList[$table]) == 0) {
				$accessList[$table] = ['*'=>'*'];
				continue;
			}

			foreach ($accessList[$table] as $k=>$uidOrUsername) {
				if (is_numeric($uidOrUsername)) continue;
				$user = $this->userCache[$table][$uidOrUsername] ?? false;
				if (!$user) $user = \nn\t3::Db()->findOneByValues($table, [$field=>$uidOrUsername]);
				if (!$user) {
					unset($accessList[$table][$k]);
					continue;
				};
				$this->userCache[$table][$uidOrUsername] = $user;
				$accessList[$table][$k] = $user['uid'];
			}
			$vals = $accessList[$table];
			$accessList[$table] = array_combine( $vals, $vals );
		}

		return $accessList;
	}

}
