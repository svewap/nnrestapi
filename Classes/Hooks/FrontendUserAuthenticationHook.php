<?php

namespace Nng\Nnrestapi\Hooks;

/**
 * 
 */
class FrontendUserAuthenticationHook {

	/**
	 * Hook called in `/sysext/core/Classes/Authentication/AbstractUserAuthentication.php`.
	 * It was Registered in `ext_localconf.php`.
	 * 
	 * The `AbstractUserAuthentication` will run through all default steps to authenticate
	 * a Frontend-User, e.g. checking `fe_typo_user`-cookie, `logintype=login` etc.
	 * At the end of the process a hook is called. It the TYPO3 source is commented with: 
	 * `Hook for alternative ways of filling the $this->user array`. This hook is not documented
	 * but seems to be the only hook available to authenticate a FrontendUser using alternative
	 * methods aside of cookie-based authentication.
	 * 
	 * This hook still exists in TYPO3 v11. In case it is removed, this script will have to be
	 * refactored. Possible the only way to get JWT working will be by XCLASSing the 
	 * `AbstractUserAuthentication->checkAuthentication()`.
	 * 
	 * @param array $params 
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $parent 
	 * @return void
	 */
	public function postUserLookUp( &$params, &$parent = null ) {

		// if logintype is not Frontend: Abort.
		if (!$parent || $parent->loginType != 'FE') return;

		// if we are not in a frontend context: Abort.
		if (!\nn\rest::Environment()->isFrontend()) return;

		// get list of all registered Authenticators registered in `ext_localconf.php` via `\nn\rest::Auth()->register();`
		$authenticators =  \nn\rest::Auth()->getAll();

		// delete expired sessions in table `nnrestapi_sessions`
		\nn\rest::Session()->removeExpiredTokens();
		
		// call `process()` on all registered Authenticators
		foreach ($authenticators as $authenticator) {
			if ($classInstance = \nn\t3::injectClass( $authenticator['className'] ?? false )) {
				if ($user = $classInstance->process()) {
					$params['pObj']->user = $user;
					break;
				}
			} else {
				\nn\t3::Exception( "nnrestapi: Registered authenticator {$authenticator['className']} not found." );
			}
		}
		
	}
}