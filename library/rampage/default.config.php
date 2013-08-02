<?php

use rampage\core\services\DIPluginServiceFactory;
return array(
    'modules' => array(),
    'module_listener_options' => array(
        'extra_config' => array(
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

            'service_manager' => array(
                'invokables' => array(
                    'rampage.core.service.DIAbstractServiceFactory' => 'rampage\core\service\DIAbstractServiceFactory',
                    'db.profiler' => 'rampage.db.NullProfiler'
                ),
                'factories' => array(
                    'Application' => 'rampage\core\services\ApplicationFactory',
                    'rampage.core.view.ViewInitializer' => 'rampage\core\services\ViewInitializerFactory',
                    'DependencyInjector' => 'rampage\core\services\DiFactory',
                    'ControllerLoader' => 'rampage\core\services\ControllerLoaderFactory',
                    'ViewHelperManager' => 'rampage\core\services\ViewHelperManagerFactory',
                    'Logger' => 'rampage\core\services\LogServiceFactory',

                    // i18n
                    'Locale' => 'rampage\core\services\LocaleFactory',
                    'Translator' => 'rampage\core\services\TranslatorServiceFactory',
                ),
                'aliases' => array(
                    // Layout
                    'rampage.Layout' => 'rampage.core.view.Layout',
                    'rampage.Theme' => 'rampage.core.resources.Theme',

                    // Core
                    'DiAbstractServiceFactory' => 'rampage.core.services.DIAbstractServiceFactory',
                    'UserConfig' => 'rampage.core.UserConfig',
                    'rampage.core.view.http.Renderer' => 'rampage\core\view\renderer\PhpRenderer',
                ),

                'abstract_factories' => array(
                    'rampage\core\service\DIAbstractServiceFactory',
                ),

                'shared' => array(
                    'rampage.core.view.ViewIntializer' => false,
                    'rampage.core.view.renderer.PhpRenderer' => false,
                    'rampage.core.view.http.Renderer' => false,
                )
            ),

            'di' => include __DIR__ . '/di.config.php',
        )
    )
);