<?php

use rampage\core\services\DIPluginServiceFactory;
return array(
    'modules' => array(),
    'module_listener_options' => array(
        'extra_config' => array(
            'service_manager' => include __DIR__ . '/service.config.php',

            'controllers' => array(
                'invokables' => array(
                    'rampage.cli.resources' => 'rampage\core\controllers\ResourcesController'
                )
            ),

            'router' => array(
                'routes' => array(
                    'rampage.core.resources' => array(
                        'type' => 'regex',
                        'options' => array(
                            'regex' => '/_res/(?<theme>[a-zA-z0-9_.-]+)/(?<scope>[a-zA-z0-9_.-]+)/(?<file>.+)$',
                            'spec' => '/_res/%theme%/%scope%/%file%',
                            'defaults' => array(
                                'controller' => 'rampage.cli.resources',
                                'action' => 'index',
                                'theme' => '',
                                'scope' => '',
                                'file' => ''
                            )
                        )
                    )
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
                ),
                'aliases' => array(
                    '__' => 'translateargs'
                )
            ),

            'di' => include __DIR__ . '/di.config.php',
        )
    )
);
