<?php

return array(
    'modules' => array(),
    'module_listener_options' => array(
        'extra_config' => array(
            'controllers' => array(
                'invokables' => array(
                    'rampage.core.layoutonly' => 'rampage\core\controller\LayoutOnlyController',
                )
            ),

            'route_manager' => array(
                'invokables' => array(
                    'rampage.route.standard' => 'rampage\core\router\http\StandardRoute',
                    'rampage.route.layout' => 'rampage\core\router\http\LayoutRoute',
                )
            ),

            'packages' => array(
                'aliases' => array(
                    'rampage.auth.AuthService' => 'rampage.auth.models.AuthenticationService',
                    'rampage.orm.db.Adapter' => 'Zend\Db\Adapter\Adapter'
                )
            ),

            'service_manager' => array(
                'invokables' => array(
                    'rampage.core.service.DiAbstractServiceFactory' => 'rampage\core\service\DiAbstractServiceFactory',
                ),
                'factories' => array(
                    'Application' => 'rampage\core\service\ApplicationFactory',
                    'rampage.core.view.ViewInitializer' => 'rampage\core\service\ViewInitializerFactory',
                    'DependencyInjector' => 'rampage\core\service\DiFactory',
                    'ObjectManager' => 'rampage\core\service\ObjectManagerFactory',
                    'ControllerLoader' => 'rampage\core\service\ControllerLoaderFactory',
                    'ViewHelperManager' => 'rampage\core\service\ViewHelperManagerFactory',
                    'Translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
                ),
                'aliases' => array(
                    // Layout
                    'rampage.Layout' => 'rampage.core.view.Layout',
                    'rampage.Theme' => 'rampage.core.resource.Theme',

                    // Resources
                    'rampage.resource.FileLocator' => 'rampage\core\resource\FileLocator',
                    // change to rampage.core.resource.UrlLocator to disable caching proxy
                    'rampage.resource.UrlLocator' => (isset($_SERVER['RAMPAGE_DEVELOPMENT']) && $_SERVER['RAMPAGE_DEVELOPMENT'])? 'rampage.core.resource.UrlLocator' : 'rampage.core.resource.url.locator.CachingProxy',
                    'rampage.resource.BootstapListener' => 'rampage.core.resource.BootstrapListener',

                    // core
                    'om' => 'ObjectManager',
                    'rampage.ObjectManager' => 'ObjectManager',
                    'repositorymanager' => 'rampage.orm.RepositoryManager',
                    'DiAbstractServiceFactory' => 'rampage.core.service.DiAbstractServiceFactory',
                    'rampage.core.view.http.Renderer' => 'rampage\core\view\renderer\PhpRenderer',

                    // ORM
                    'rampage.orm.db.AdapterManager' => 'rampage.orm.db.adapter.AdapterManager',
                    'rampage.orm.db.PlatformManager' => 'rampage.orm.db.platform.ServiceLocator',

                    // URLS
                    'rampage.url.base' => 'rampage.core.model.Url',
                    'rampage.url.media' => 'rampage.core.model.url.Media'
                ),

                'abstract_factories' => array(
                    'rampage\core\service\DiAbstractServiceFactory'
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