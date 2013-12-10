<?php

return array(
    'invokables' => array(
        'rampage\core\services\DIAbstractServiceFactory' => 'rampage\core\services\DIAbstractServiceFactory',
        'rampage\core\services\ViewResolverDelegator' => 'rampage\core\services\ViewResolverDelegator',
    ),
    'factories' => array(
        'Application' => 'rampage\core\services\ApplicationFactory',
        'DependencyInjector' => 'rampage\core\services\DIFactory',
        'ControllerLoader' => 'rampage\core\services\ControllerLoaderFactory',
        'Logger' => 'rampage\core\services\LogServiceFactory',

        // View/Layout
        'rampage.UserConfig' => 'rampage\core\services\UserConfigFactory',

        // i18n
        'Locale' => 'rampage\core\services\LocaleFactory',
        'Translator' => 'rampage\core\services\TranslatorServiceFactory',

        // Resources
        'rampage.ResourceLocator' => 'rampage\core\services\ResourceLocatorFactory',
        'rampage.Theme' => 'rampage\core\services\ThemeFactory',
        'rampage.ResourcePublishingStrategy' => 'rampage\core\services\ResourcePublishingStrategyFactory',

        // Doctrine
        'doctrine.cache.filesystem' => 'rampage\core\services\DoctrineFilesystemCacheFactory',
    ),
    'aliases' => array(
        // Layout
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
        'DiAbstractServiceFactory' => 'rampage\core\services\DIAbstractServiceFactory',
    ),

    'delegators' => array(
        'ViewResolver' => array(
            'rampage\core\services\ViewResolverDelegator'
        ),
    ),

    'abstract_factories' => array(
        'rampage\core\services\DIAbstractServiceFactory',
    ),
);
