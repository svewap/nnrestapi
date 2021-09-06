<?php 

return [
    'frontend' => [
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