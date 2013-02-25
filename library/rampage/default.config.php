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
            'router' => array(
                'route_plugins' => array(
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
                'factories' => array(
                    'Application' => 'rampage\core\service\ApplicationFactory',
                    'rampage.core.view.ViewInitializer' => 'rampage\core\service\ViewInitializerFactory',
                    'DependencyInjector' => 'rampage\core\service\DiFactory',
                    'ObjectManager' => 'rampage\core\service\ObjectManagerFactory'
                ),
                'aliases' => array(
                    'rampage.Layout' => 'rampage.core.view.Layout',
                    'rampage.Theme' => 'rampage.core.resource.Theme',
                    'rampage.resource.FileLocator' => 'rampage\core\resource\FileLocator',
                    'rampage.core.view.http.Renderer' => 'rampage\core\view\renderer\PhpRenderer',
                    'rampage.resource.BootstapListener' => 'rampage.core.resource.BootstrapListener',
                    'om' => 'ObjectManager',
                    'rampage.ObjectManager' => 'ObjectManager',
                ),
                'abstract_factories' => array(
                    'rampage\core\service\DiAbstractServiceFactory'
                ),
                'shared' => array(
                    'rampage.core.resource.FileLocator' => false,
                    'rampage.core.resource.Theme' => false,
                    'rampage.core.view.ViewIntializer' => false,
                    'rampage.core.view.renderer.PhpRenderer' => false,
                    'rampage.core.view.http.Renderer' => false,
                    'rampage.core.resource.BootstrapListener' => false,
                )
            ),

            'di' => include __DIR__ . '/di.config.php',
        )
    )
);