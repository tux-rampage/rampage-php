<?php

return array(
    'modules' => array(),
    'module_listener_options' => array(
        'extra_config' => array(
            'controllers' => array(
                'invokables' => array(
                    'rampage.core.layoutonly' => 'rampage\core\controller\LayoutOnlyController',
                    'rampage.orm.db.setup' => 'rampage\orm\db\controllers\SetupController',
                )
            ),

            'route_manager' => array(
                'invokables' => array(
                    'rampage.route.standard' => 'rampage\core\router\http\StandardRoute',
                    'rampage.route.layout' => 'rampage\core\router\http\LayoutRoute',
                )
            ),

            'controller_plugins' => array(
                'factories' => array(
                    'url' => 'rampage\core\controller\UrlPluginFactory'
                )
            ),

            'packages' => array(
                'aliases' => array(
                    'rampage.auth.AuthService' => 'rampage.auth.models.AuthenticationService',
                    'rampage.orm.db.Adapter' => 'rampage.db.Adapter',
                )
            ),

            'rampage' => array(
                'events' => array(
                    'rampage\core\Application' => array(
                        'bootstrap' => array(
                            'rampage.design' => array(
                                'listener' => 'rampage.core.model.design.BootstrapListener',
                                'priority' => 10000
                            ),
                        ),
                    ),
                ),
            ),

            'service_manager' => array(
                'invokables' => array(
                    'rampage.core.service.DiAbstractServiceFactory' => 'rampage.core.service.DiAbstractServiceFactory',
                    'db.profiler' => 'rampage.db.NullProfiler'
                ),
                'factories' => array(
                    'Application' => 'rampage\core\service\ApplicationFactory',
                    'rampage.core.view.ViewInitializer' => 'rampage\core\service\ViewInitializerFactory',
                    'DependencyInjector' => 'rampage\core\service\DiFactory',
                    'ObjectManager' => 'rampage\core\service\ObjectManagerFactory',
                    'ControllerLoader' => 'rampage\core\service\ControllerLoaderFactory',
                    'ViewHelperManager' => 'rampage\core\service\ViewHelperManagerFactory',
                    'Logger' => 'rampage\core\log\LogServiceFactory',

                    // i18n
                    'Locale' => 'rampage\core\service\LocaleFactory',
                    'Translator' => 'rampage\core\service\TranslatorServiceFactory',
                ),
                'aliases' => array(
                    // Layout
                    'rampage.Layout' => 'rampage.core.view.Layout',
                    'rampage.Theme' => 'rampage.core.resource.Theme',
                    'rampage.theme.Config' => 'rampage.core.model.design.Config',

                    // Resources
                    'rampage.resource.FileLocator' => 'rampage\core\resource\FileLocator',
                    'rampage.resource.ResourceLocator' => 'rampage.Theme',
//                     'rampage.resource.locator.MapProxy' => 'rampage.core.resource.file.locator.MapProxy',
                    'rampage.resource.UrlLocator' => 'rampage.core.resource.UrlLocator',
//                     'rampage.resource.UrlLocator' => 'rampage.core.resource.url.locator.MapProxy',
                    'rampage.resource.BootstapListener' => 'rampage.core.resource.BootstrapListener',

                    // Core
                    'rampage.ObjectManager' => 'ObjectManager',
                    'RepositoryManager' => 'rampage.orm.RepositoryManager',
                    'DiAbstractServiceFactory' => 'rampage.core.service.DiAbstractServiceFactory',
                    'rampage.event.Config' => 'rampage.core.event.Config',
                    'rampage.UserConfig' => 'rampage.core.model.Config',
                    'rampage.core.view.http.Renderer' => 'rampage\core\view\renderer\PhpRenderer',

                    // ORM
                    'rampage.orm.Config' => 'rampage.orm.Config',
                    'rampage.orm.db.AdapterManager' => 'rampage.orm.db.adapter.AdapterManager',
                    'rampage.orm.db.PlatformManager' => 'rampage.orm.db.platform.ServiceLocator',

                    // URLS
                    'rampage.url.base' => 'rampage.core.model.Url',
                    'rampage.url.media' => 'rampage.core.model.url.Media'
                ),

                'abstract_factories' => array(
                    'rampage\core\service\DiAbstractServiceFactory',
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