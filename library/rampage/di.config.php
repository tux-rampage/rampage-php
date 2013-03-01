<?php

return array(
    'definition' => array(
        'runtime' => array(
            'enabled' => true
        ),
        'compiler' => (is_readable(__DIR__ . '/di.compiled.php'))? array(__DIR__ . '/di.compiled.php') : array(),
        'class' => array()
    ),
    'instance' => array(
        'preferences' => array(
            'rampage\core\ObjectManagerInterface' => 'ObjectManager',
            'rampage\core\PathManager' => 'rampage.PathManager',
            'rampage\core\ModuleManager' => 'rampage.ModuleManager',
            'rampage\core\resource\Theme' => 'rampage.Theme',
            'rampage\core\resource\FileLocatorInterface' => 'rampage.Theme',
            'rampage\core\view\Layout' => 'rampage.Layout',
            'rampage\core\resource\UrlLocatorInterface' => 'rampage.core.resource.UrlLocator',

            // ORM
            'rampage\orm\ConfigInterface' => 'rampage\orm\Config',
            'rampage\orm\entity\type\ConfigInterface' => 'rampage\orm\Config',
            'rampage\orm\db\adapter\ConfigInterface' => 'rampage\orm\db\Config',
            'rampage\orm\db\platform\ConfigInterface' => 'rampage\orm\db\Config',
            'rampage\orm\db\adapter\AdapterManager' => 'rampage.orm.db.AdapterManager',
            'rampage\orm\db\platform\ServiceLocator' => 'rampage.orm.db.PlatformManager',

        ),

        'rampage\core\resource\Theme' => array(
            'parameters' => array(
                'fallback' => 'rampage.resource.FileLocator',
            )
        ),

        'rampage\auth\service\AuthServiceManager' => array(
            'preferences' => array(
                // TODO
            )
        )
    )
);