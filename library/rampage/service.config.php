<?php

return array(
    'invokables' => array(
        'rampage.core.services.DIAbstractServiceFactory' => 'rampage\core\services\DIAbstractServiceFactory',
        'db.profiler' => 'rampage.db.NullProfiler'
    ),
    'factories' => array(
        'Application' => 'rampage\core\services\ApplicationFactory',
        'DependencyInjector' => 'rampage\core\services\DIFactory',
        'ControllerLoader' => 'rampage\core\services\ControllerLoaderFactory',
        'ViewResolver' => 'rampage\core\services\ViewResolverFactory',
        //'ViewHelperManager' => 'rampage\core\services\ViewHelperManagerFactory',
        'Logger' => 'rampage\core\services\LogServiceFactory',

        // View/Layout
        'rampage.ViewInitializer' => 'rampage\core\services\ViewInitializerFactory',
        'rampage.ViewLocator' => 'rampage\core\services\ViewLocatorFactory',
        'rampage.UserConfig' => 'rampage\core\services\UserConfigFactory',
        'rampage.Layout' => 'rampage\core\services\LayoutFactory',
        'rampage.core.view.HttpRenderer' => 'rampage\core\services\HttpRendererFactory',

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
        'rampage\core\view\ViewLocator' => 'rampage.ViewLocator',
        'Zend\View\HelperPluginManager' => 'ViewHelperManager',

        // Resources
        'rampage\core\PathManager' => 'rampage.PathManager',
        'rampage\core\resources\Theme' => 'rampage.Theme',
        'rampage\core\resources\FileLocator' => 'rampage.ResourceLocator',
        'rampage\core\resources\StaticResourcePublishingStrategy' => 'rampage.ResourcePublishingStrategy',

        // Core
        'UserConfig' => 'rampage.UserConfig',
        'rampage\core\UserConfig' => 'rampage.UserConfig',

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
        'rampage\core\services\DIAbstractServiceFactory',
    ),

    'shared' => array(
        'rampage.core.view.ViewIntializer' => false,
        'rampage.core.view.renderer.PhpRenderer' => false,
        'rampage.core.view.HttpRenderer' => false,
    )
);
