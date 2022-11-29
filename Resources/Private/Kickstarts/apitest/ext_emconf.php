<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Rest-Api example',
    'description' => 'Example extension for EXT:nnrestapi',
    'category' => 'frontend',
    'author' => 'yourcompany.de',
    'author_email' => 'your@email.de',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-12.9.99',
            'nnhelpers' => '2.0.0-0.0.0',
            'nnrestapi' => '2.0.0-0.0.0',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
