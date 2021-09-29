<?php 

return [
    'frontend' => [
        'nnrestapi/requestparser' => [
            'target' => \Nng\Nnrestapi\Middleware\NnrestapiRequestParser::class,
            'before' => [
                'typo3/cms-frontend/timetracker',
            ],
        ],
        // 'nnrestapi/auth' => [
        //     'target' => \Nng\Nnrestapi\Middleware\NnrestapiAuthenticator::class,
        //     'before' => [
        //         'typo3/cms-frontend/page-resolver',
        //     ]
        // ],
        // 'nnrestapi/resolver' => [
        //     'target' => \Nng\Nnrestapi\Middleware\NnrestapiResolver::class,
        //     'after' => [
        //         'nnrestapi/auth'
        //     ],
        // ]
    ]
];