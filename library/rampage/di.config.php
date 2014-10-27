<?php

return array(
    'definition' => array(
        'runtime' => array(
            'enabled' => true
        ),
        'class' => array()
    ),
    'instance' => array(
        'aliases' => array(
            'rampage.ResourcePublishingStrategy' => 'rampage\core\resources\StaticResourcePublishingStrategy',
        ),
        'preferences' => array(
            'Zend\Di\DependencyInjectionInterface' => 'rampage\core\di\DIContainer',
            'rampage\core\resources\FileLocatorInterface' => 'rampage\core\resources\FileLocator',
            'rampage\core\resources\ThemeInterface' => 'rampage\core\resources\Theme',
            'rampage\core\resources\UrlLocatorInterface' => 'rampage\core\resources\UrlLocator',

            'rampage\core\url\UrlConfigInterface' => 'rampage\core\url\UrlConfig',
            'rampage\core\UserConfigInterface' => 'rampage\core\UserConfig',
            'rampage\core\resources\PublishingStrategyInterface' => 'rampage.ResourcePublishingStrategy'
        ),
    )
);
