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
                ),
                'aliases' => array(
                    'rampage.Layout' => 'rampage.core.view.Layout',
                    'rampage.Theme' => 'rampage.core.resource.Theme',
                    'rampage.resource.FileLocator' => 'rampage\core\resource\FileLocator',
                    'rampage.core.view.http.Renderer' => 'rampage\core\view\renderer\PhpRenderer',
                    'rampage.resource.BootstapListener' => 'rampage.core.resource.BootstrapListener',
                    'om' => 'ObjectManager',
                    'rampage.ObjectManager' => 'ObjectManager',
                    'repositorymanager' => 'rampage.orm.RepositoryManager',
                    'DiAbstractServiceFactory' => 'rampage.core.service.DiAbstractServiceFactory',

                    // ORM
                    'rampage.orm.db.AdapterManager' => 'rampage.orm.db.adapter.AdapterManager',
                    'rampage.orm.db.PlatformManager' => 'rampage.orm.db.platform.ServiceLocator',
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