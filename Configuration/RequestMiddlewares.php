<?php 

return [
	'frontend' => [

		// Parses the `PUT` and `DELETE` requests (usually not supported by PHP)
		'nnrestapi/requestparser' => [
			'target' => \Nng\Nnrestapi\Middleware\RequestParser::class,
			'before' => [
				'typo3/cms-frontend/timetracker',
			],
		],

		// Authenticates the frontend-user via JWT
		'nnrestapi/auth' => [
			'target' => \Nng\Nnrestapi\Middleware\Authenticator::class,
			'before' => [
				'typo3/cms-frontend/authentication',
			],
			'after' => [
				'typo3/cms-frontend/backend-user-authentication'
			]
		],

		// Resolve the request, forward to ApiController
		'nnrestapi/resolver' => [
			'target' => \Nng\Nnrestapi\Middleware\PageResolver::class,
			'after' => [
				'typo3/cms-frontend/static-route-resolver'
			],
			'before' => [
				'typo3/cms-frontend/page-resolver'
			],
		]
	]
];