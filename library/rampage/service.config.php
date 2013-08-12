<?php

return array(
    'invokables' => array(
        'rampage.core.service.DIAbstractServiceFactory' => 'rampage\core\service\DIAbstractServiceFactory',
        'db.profiler' => 'rampage.db.NullProfiler'
    ),
    'factories' => array(
        'Application' => 'rampage\core\services\ApplicationFactory',
        'DependencyInjector' => 'rampage\core\services\DiFactory',
        'ControllerLoader' => 'rampage\core\services\ControllerLoaderFactory',
        'ViewHelperManager' => 'rampage\core\services\ViewHelperManagerFactory',
        'Logger' => 'rampage\core\services\LogServiceFactory',

        // View/Layout
        'rampage.ViewInitializer' => 'rampage\core\services\ViewInitializerFactory',
        'rampage.UserConfig' => 'rampage\core\services\UserConfigFactory',
        'rampage.Layout' => 'rampage\core\services\LayoutFactory',

        // i18n
        'Locale' => 'rampage\core\services\LocaleFactory',
        'Translator' => 'rampage\core\services\TranslatorServiceFactory',

        // Resources
        'rampage.ResourceLocator' => 'rampage\core\services\ResourceLocatorFactory',
        'rampage.Theme' => 'rampage\core\services\ThemeFactory',
        'rampage.ResourcePublishingStrategy' => 'rampage\core\services\ResourcePublishingStrategyFactory',
    ),
    'aliases' => array(
        // Layout
        'rampage\core\view\Layout' => 'rampage.Layout',

        // Resources
        'rampage\core\PathManager' => 'rampage.PathManager',
        'rampage\core\resources\Theme' => 'rampage.Theme',
        'rampage\core\resources\FileLocator' => 'rampage.ResourceLocator',
        'rampage\core\resources\StaticResourcePublishingStrategy' => 'rampage.ResourcePublishingStrategy',

        // Core
        'UserConfig' => 'rampage.UserConfig',
        'rampage\core\UserConfig' => 'rampage.UserConfig',
        'rampage.core.view.HttpRenderer' => 'rampage\core\view\renderer\PhpRenderer',

        // I18n
        'Zend\I18n\Translator\Translator' => 'Translator',
        'rampage\core\i18n\Locale' => 'Locale',

        // DI
        'Di' => 'DependencyInjector',
        'Zend\Di\Di' => 'DependencyInjector',
        'rampage\core\di\DIContainer' => 'DependencyInjector',
        'DiAbstractServiceFactory' => 'rampage.core.services.DIAbstractServiceFactory',
    ),

    'abstract_factories' => array(
        'rampage\core\service\DIAbstractServiceFactory',
    ),

    'shared' => array(
        'rampage.core.view.ViewIntializer' => false,
        'rampage.core.view.renderer.PhpRenderer' => false,
        'rampage.core.view.HttpRenderer' => false,
    )
);
