<?php

use rampage\core\di\ServiceType;
$conf = array(
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
            'rampage\core\ModuleRegistry' => 'rampage.ModuleRegistry',
            'rampage\core\resource\Theme' => 'rampage.Theme',
            'rampage\core\resource\FileLocatorInterface' => 'rampage.resource.locator.MapProxy',
            'rampage\core\model\design\Config' => 'rampage.theme.Config',
            'rampage\core\view\Layout' => 'rampage.Layout',
            'rampage\core\view\helper\PluginManager' => 'ViewHelperManager',
            'rampage\core\resource\UrlLocatorInterface' => 'rampage.resource.UrlLocator',
            'rampage\core\model\Config' => 'rampage.UserConfig',

            // Zend
            'Zend\View\HelperPluginManager' => 'ViewHelperManager',
            'Zend\Db\Adapter\Profiler\ProfilerInterface' => 'db.profiler',
            'Zend\Log\LoggerInterface' => 'Logger',

            // ORM
            'rampage\orm\ConfigInterface' => 'rampage.orm.Config',
            'rampage\orm\RepositoryManager' => 'RepositoryManager',
            'rampage\orm\entity\type\ConfigInterface' => 'rampage.orm.Config',
            'rampage\orm\db\adapter\ConfigInterface' => 'rampage.orm.db.Config',
            'rampage\orm\db\platform\ConfigInterface' => 'rampage.orm.db.Config',
            'rampage\orm\db\adapter\AdapterManager' => 'rampage.orm.db.AdapterManager',
            'rampage\orm\db\platform\ServiceLocator' => 'rampage.orm.db.PlatformManager',

            // URLs
            'rampage\core\model\Url' => 'rampage.url.base',
            'rampage\core\model\url\Media' => 'rampage.url.media',

            // I18n
            'Zend\I18n\Translator\Translator' => 'Translator',
            'rampage\core\i18n\Locale' => 'Locale',
        ),

        'rampage\core\resource\Theme' => array(
            'parameters' => array(
                'fallback' => new ServiceType('rampage.resource.FileLocator'),
            )
        ),

        'rampage\core\model\design\Config' => array(
            'parameters' => array(
                'data' => new ServiceType('Config')
            )
        ),

        // Map proxy - Parent should not be the type preference which could lead to cycle dependency
        // to the map proxy itself
        'rampage\core\resource\url\locator\MapProxy' => array(
            'parameters' => array(
                'parent' => new ServiceType('rampage.core.resource.UrlLocator')
            ),
        ),

        // Same as for url map proxy
        'rampage\core\resource\file\locator\MapProxy' => array(
            'parameters' => array(
                'parent' => new ServiceType('rampage.Theme'),
            ),
        ),

        'rampage\auth\service\AuthServiceManager' => array(
            // TODO
        )
    )
);

foreach ($conf['instance']['preferences'] as $key => $value) {
    $conf['instance']['preferences'][$key] = new ServiceType($value);
}

return $conf;