<?php

return [
    APP_URI => [
        '/seo[/]' => [
            'controller' => 'Phire\Seo\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'seo',
                'permission' => 'index'
            ]
        ]
    ]
];
