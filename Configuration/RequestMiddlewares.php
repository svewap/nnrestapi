<?php 

return [
    'frontend' => [
        'nnrestapi/requestparser' => [
            'target' => \Nng\Nnrestapi\Middleware\NnrestapiRequestParser::class,
            'before' => [
                'typo3/cms-frontend/timetracker',
            ],
        ],
        'nnrestapi' => [
            'target' => \Nng\Nnrestapi\Middleware\NnrestapiResolver::class,
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
                'typo3/cms-frontend/tsfe',
                'typo3/cms-frontend/page-resolver',
            ],
        ]
    ]
];