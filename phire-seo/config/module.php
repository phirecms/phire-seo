<?php
/**
 * Module Name: phire-seo
 * Author: Nick Sagona
 * Description: This is the SEO media module for Phire CMS 2
 * Version: 1.0
 */
return [
    'phire-seo' => [
        'prefix'     => 'Phire\Seo\\',
        'src'        => __DIR__ . '/../src',
        'routes'     => include 'routes.php',
        'resources'  => include 'resources.php',
        'nav.module' => [
            'name' => 'SEO',
            'href' => '/seo',
            'acl' => [
                'resource'   => 'seo',
                'permission' => 'index'
            ]
        ],
        'events' => [
            [
                'name'     => 'app.route.pre',
                'action'   => 'Phire\Seo\Event\Seo::bootstrap',
                'priority' => 1000
            ],
            [
                'name'     => 'app.send.pre',
                'action'   => 'Phire\Seo\Event\Seo::init',
                'priority' => 1000
            ],
            [
                'name'     => 'app.send.post',
                'action'   => 'Phire\Seo\Event\Seo::parse',
                'priority' => 1000
            ]
        ],
        'install' => function() {
            $config = new \Phire\Table\Config([
                'setting' => 'seo_config',
                'value'   => ''
            ]);
            $config->save();
            $config = new \Phire\Table\Config([
                'setting' => 'seo_analysis',
                'value'   => ''
            ]);
            $config->save();
        },
        'uninstall' => function() {
            $config = \Phire\Table\Config::findById('seo_config');
            if (isset($config->setting)) {
                $config->delete();
            }
            $config = \Phire\Table\Config::findById('seo_analysis');
            if (isset($config->setting)) {
                $config->delete();
            }
        }
    ]
];
