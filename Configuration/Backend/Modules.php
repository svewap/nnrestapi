<?php

return [
    'nnrestapi' => [
        'parent' => 'web',
        'position' => ['bottom'],
        'access' => 'user,group',
        'workspaces' => 'live',
        'icon' => 'EXT:nnrestapi/Resources/Public/Icons/Extension.svg',
        'path' => '/module/web/nnrestapi',
        'labels' => 'LLL:EXT:nnrestapi/Resources/Private/Language/locallang_mod1.xml',
        'extensionName' => 'nnrestapi',
        'navigationComponent' => '',
        'inheritNavigationComponentFromMainModule' => false,
        'controllerActions' => [
            \Nng\Nnrestapi\Controller\ModController::class => [
                'index', 'kickstart', 'readme'
            ],
        ],
    ],
];