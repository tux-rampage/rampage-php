<?php

namespace rampage\core;

use rampage\core\services\DIPluginServiceFactory;

return array(
    'service_manager' => require __DIR__ . '/service.config.php',
    'controllers' => array(
        'invokables' => array(
            'rampage.cli.resources' => 'rampage\core\controllers\ResourcesController'
        )
    ),

    'router' => array(
        'routes' => array(
            'rampage.core.resources' => new resources\ResourceRoute('_res',array(
                'controller' => 'rampage.cli.resources',
                'action' => 'index'
            ))
        ),
    ),

    'controller_plugins' => array(
        'factories' => array(
            'url' => 'rampage\core\controllers\UrlPluginFactory'
        )
    ),

    'view_helpers' => array(
        'factories' => array(
            'resourceurl' => new DIPluginServiceFactory('rampage\core\view\helpers\ResourceUrlHelper'),
            'translateargs' => new DIPluginServiceFactory('rampage\core\view\helpers\TranslatorHelper'),
            'url' => 'rampage\core\view\helpers\UrlHelperFactory',
            'baseUrl' => new DIPluginServiceFactory('rampage\core\view\helpers\BaseUrlHelper'),
        ),
        'aliases' => array(
            '__' => 'translateargs'
        )
    ),

    'di' => include __DIR__ . '/di.config.php',
);
