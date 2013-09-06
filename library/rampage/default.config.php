<?php

use rampage\core\services\DIPluginServiceFactory;
return array(
    'modules' => array(),
    'module_listener_options' => array(
        'extra_config' => array(
            'service_manager' => include __DIR__ . '/service.config.php',

            'controllers' => array(
                'invokables' => array(
                    'rampage.core.layoutonly' => 'rampage\core\controllers\LayoutOnlyController',
                )
            ),

            'route_manager' => array(
                'invokables' => array(
                    'rampage.route.standard' => 'rampage\core\routers\http\StandardRoute',
                    'rampage.route.layout' => 'rampage\core\routers\http\LayoutRoute',
                )
            ),

            'controller_plugins' => array(
                'factories' => array(
                    'url' => 'rampage\core\controllers\UrlPluginFactory'
                )
            ),

            'view_helpers' => array(
                'factories' => array(
                    'resourceurl' => new DIPluginServiceFactory('rampage\core\view\helpers\ResourceUrlHelper'),
                    'url' => new DIPluginServiceFactory('rampage\core\view\helpers\UrlHelper'),
                    'translateargs' => new DIPluginServiceFactory('rampage\core\view\helpers\TranslatorHelper'),
                )
            ),

            'di' => include __DIR__ . '/di.config.php',
        )
    )
);