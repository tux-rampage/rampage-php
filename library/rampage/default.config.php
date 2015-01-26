<?php

namespace rampage\core;

use rampage\core\services\DIPluginServiceFactory;

return [
    'service_manager' => require __DIR__ . '/service.config.php',
    'controllers' => [
        'invokables' => [
            'rampage.cli.resources' => 'rampage\core\controllers\ResourcesController'
        ]
    ],

    'rampage' => [
        'resources' => [
            'rampage.core' => __DIR__ . '/../../resources',
        ]
    ],

    'router' => [
        'routes' => [
            'rampage.core.resources' => new resources\ResourceRoute('_res', [
                'controller' => 'rampage.cli.resources',
                'action' => 'index'
            ])
        ],
    ],

    'controller_plugins' => [
        'factories' => [
            'url' => 'rampage\core\controllers\UrlPluginFactory'
        ]
    ],

    'view_helpers' => [
        'factories' => [
            'resourceurl' => new DIPluginServiceFactory('rampage\core\view\helpers\ResourceUrlHelper'),
            'translateargs' => new DIPluginServiceFactory('rampage\core\view\helpers\TranslatorHelper'),
            'url' => 'rampage\core\view\helpers\UrlHelperFactory',
            'baseUrl' => new DIPluginServiceFactory('rampage\core\view\helpers\BaseUrlHelper'),
            'requireJs' => 'rampage\core\view\helpers\RequireJsHelperFactory',
        ],
        'aliases' => [
            '__' => 'translateargs'
        ]
    ],

    'di' => include __DIR__ . '/di.config.php',
];
