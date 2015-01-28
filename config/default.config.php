<?php

namespace rampage\core;

use rampage\core\services\DIPluginServiceFactory;

return [
    'service_manager' => require __DIR__ . '/service.config.php',
    'di' => include __DIR__ . '/di.config.php',

    'controllers' => [
        'invokables' => [
            'rampage.cli.resources' => controllers\ResourcesController::class
        ]
    ],

    'rampage' => [
        'resources' => [
            'rampage.core' => __DIR__ . '/../resources',
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
            'url' => controllers\UrlPluginFactory::class,
        ]
    ],

    'view_helpers' => [
        'factories' => [
            'resourceurl' => new DIPluginServiceFactory(view\helpers\ResourceUrlHelper::class),
            'translateargs' => new DIPluginServiceFactory(view\helpers\TranslatorHelper::class),
            'url' => view\helpers\UrlHelperFactory::class,
            'baseUrl' => new DIPluginServiceFactory(view\helpers\BaseUrlHelper::class),
            'requireJs' => view\helpers\RequireJsHelperFactory::class,
        ],
        'aliases' => [
            '__' => 'translateargs'
        ]
    ],
];
