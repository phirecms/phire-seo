<?php

return [
    '/robots.txt' => [
        'controller' => 'Phire\Seo\Controller\IndexController',
        'action'     => 'robots',
    ],
    APP_URI => [
        '/seo[/]' => [
            'controller' => 'Phire\Seo\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'seo',
                'permission' => 'index'
            ]
        ],
        '/seo/analysis[/]' => [
            'controller' => 'Phire\Seo\Controller\IndexController',
            'action'     => 'analysis',
            'acl'        => [
                'resource'   => 'seo',
                'permission' => 'analysis'
            ]
        ],
        '/seo/json[/]' => [
            'controller' => 'Phire\Seo\Controller\IndexController',
            'action'     => 'json',
            'acl'        => [
                'resource'   => 'seo',
                'permission' => 'json'
            ]
        ]
    ]
];
