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
            'rampage\orm\db\AdapterConfigInterface' => 'rampage\orm\db\AdapterConfig',
        ),
        'rampage\auth\service\AuthServiceManager' => array(
            'preferences' => array(
                ''
            )
        )
    )
);