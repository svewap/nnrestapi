<?php 

/**
 * Decide, when to process the API request.
 * 
 * This can either be before or after initializing the TSFE, depending on:
 * - The settings made in the extension manager
 * - The version of TYPO3
 * 
 * In TYPO3 9 the TSFE __MUST__ be present, otherwise the domain-models (e.g. the `FileReferences` 
 * will not be available and an error will be thrown.
 * 
 * In TYPO3 10+ the Middleware can be called __BEFORE__ the TSFE is initialized, as the domain-models
 * are already available at a earlier point of the booting-process. By setting the checkbox in
 * the extension manager you can force TYPO3 10+ to wait for the TSFE. This might make sense, if
 * certain methods or entities are still missing.
 * 
 */
$emConf = \nn\t3::Environment()->getExtConf('nnrestapi');
$needsTsfe = $emConf['tsfe'] || \nn\t3::t3Version() < 10;

$pageResolverCallpoint = [
	'before' 	=> $needsTsfe ? 'typo3/cms-frontend/shortcut-and-mountpoint-redirect' : 'typo3/cms-frontend/page-resolver',
	'after' 	=> $needsTsfe ? 'typo3/cms-frontend/prepare-tsfe-rendering' : 'typo3/cms-frontend/static-route-resolver',
];

return [
	'frontend' => [

		// Parses the `PUT` and `DELETE` requests (usually not supported by PHP)
		'nnrestapi/requestparser' => [
			'target' => \Nng\Nnrestapi\Middleware\RequestParser::class,
			'before' => [
				'typo3/cms-frontend/timetracker',
			],
		],

		// Resolve the request, forward to ApiController
		'nnrestapi/resolver' => [
			'target' => \Nng\Nnrestapi\Middleware\PageResolver::class,
			'before' => [
				$pageResolverCallpoint['before'],
			],
			'after' => [
				$pageResolverCallpoint['after'],
			],
		]
	]
];