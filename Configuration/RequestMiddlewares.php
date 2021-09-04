<?php 

return [
    'frontend' => [
        'nnrestapi' => [
            'target' => \Nng\Nnrestapi\Middleware\NnrestapiResolver::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ]
    ]
];