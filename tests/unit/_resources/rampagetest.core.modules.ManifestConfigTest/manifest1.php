<?php

if (!isset($tmpPath)) {
    $tmpPath = null;
    throw new RuntimeException('$tmpPath is undefined!');
}

return array(
    'application_config' => array(
        'rampage' => array(
            'layout' => array(
                'files' => array(
                    'foo.bar::layout.xml' => 0,
                    'foo.bar::something.xml' => 0
                ),
            ),

            'resources' => array(
                'foo.bar' => array('base' => $tmpPath . '/resource'),
                'bar.baz' => array('base' => $tmpPath . '/baz.resources')
            )
        ),

        'packages' => array(
            'aliases' => array(
                'some.Class' => 'foo.bar.MyClass'
            )
        ),

        'service_manager' => array(
            'factories' => array(
                'foo.test' => 'foo\bar\TestFactory'
            ),
            'abstract_factories' => array(
                'abstract.foo.test' => 'foo\bar\TestAbstractFactory'
            ),
            'invokables' => array(
                'foo.testservice' => 'foo.bar.TestService'
            ),
            'shared' => array('foo.testservice' => false),
            'aliases' => array(
                'foo.Abc' => 'my.custom.Abc'
            ),
            'initializers' => array(
                'foo\bar\TestInit'
            )
        ),

        'translator' => array(
            'translation_patterns' => array(array(
                'type' => 'gettext',
                'basedir' => $tmpPath . '/res/locale',
                'pattern' => '%s.dat'
            ))
        ),

        'controllers' => array(
            'invokables' => array(
                'foo.bar.index' => 'foo\bar\controllers\IndexController',
                'foo.bar.list' => 'foo\bar\controllers\ListController',
            ),
        ),

        'router' => array(
            'routes' => array(
                'foo.bar.default' => array(
                    'type' => 'rampage.route.standard',
                    'frontname' => 'foo',
                    'namespace' => 'foo.bar',
                    'allowed_params' => array('id' => 'id', 'type' => 'type'),
                    'defaults' => array()
                ),
                'foo.bar.info' => array(
                    'type' => 'rampage.route.layout',
                    'route' => '/fooinfo',
                    'layout' => 'a',
                    'handles' => array('b' => 'b', 'c' => 'c')
                )
            )
        )
    ),
    'autoloader_config' => array(
        'Zend\Loader\StandardAutoloader' => array(
            'namespaces' => array(
                'foo\bar' => $tmpPath . '/src'
            )
        )
    )
);