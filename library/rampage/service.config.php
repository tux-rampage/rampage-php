<?php

namespace rampage\core;

return [
    'invokables' => [
        services\DIAbstractServiceFactory::class => services\DIAbstractServiceFactory::class,
        services\ViewResolverDelegator::class => services\ViewResolverDelegator::class,
    ],
    'factories' => [
        'Application' => services\ApplicationFactory::class,
        'DependencyInjector' => services\DIFactory::class,
        'ControllerLoader' => services\ControllerLoaderFactory::class,

        // i18n
        'Locale' => services\LocaleFactory::class,
        'Translator' => services\TranslatorServiceFactory::class,

        // Resources
        'rampage.ResourceLocator' => services\ResourceLocatorFactory::class,
        'rampage.Theme' => services\ThemeFactory::class,
        'rampage.ResourcePublishingStrategy' => services\ResourcePublishingStrategyFactory::class,

        resources\UrlLocator::class => services\ResourceUrlLocatorFactory::class,

        // Doctrine
        'doctrine.cache.filesystem' => services\DoctrineFilesystemCacheFactory::class,
    ],
    'aliases' => [
        // Layout
        'Zend\View\HelperPluginManager' => 'ViewHelperManager',

        // Resources
        PathManager::class => 'rampage.PathManager',
        resources\Theme::class => 'rampage.Theme',
        resources\FileLocator::class => 'rampage.ResourceLocator',

        // I18n
        'Zend\I18n\Translator\Translator' => 'Translator',
        'Zend\I18n\Translator\TranslatorInterface' => 'Translator',
        i18n\Locale::class => 'Locale',

        // DI
        'Di' => 'DependencyInjector',
        'Zend\Di\Di' => 'DependencyInjector',
        di\DIContainer::class => 'DependencyInjector',
        'DiAbstractServiceFactory' => services\DIAbstractServiceFactory::class,
    ],

    'delegators' => [
        'ViewResolver' => [
            services\ViewResolverDelegator::class
        ]
    ],

    'abstract_factories' => [
        services\DIAbstractServiceFactory::class,
        services\BaseUrlAbstractFactory::class,
    ],
];
