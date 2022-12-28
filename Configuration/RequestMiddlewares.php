<?php 

$pageResolverCallpoint = [
	'before' 	=> 'typo3/cms-frontend/content-length-headers',
	'after' 	=> 'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
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