<?php
/**
 * Copyright (c) 2015 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core;

use Zend\Di\DependencyInjectionInterface;


return [
    'definition' => [
        'runtime' => [
            'enabled' => true
        ],
        'class' => [],
    ],
    'instance' => [
        'aliases' => [
            'rampage.ResourcePublishingStrategy' => resources\StaticResourcePublishingStrategy::class,
        ],
        'preferences' => [
            DependencyInjectionInterface::class => di\DIContainer::class,
            resources\FileLocatorInterface::class => resources\FileLocator::class,
            resources\ThemeInterface::class => resources\Theme::class,
            resources\UrlLocatorInterface::class => resources\UrlLocator::class,
            resources\PublishingStrategyInterface::class => 'rampage.ResourcePublishingStrategy'
        ],
    ]
];
