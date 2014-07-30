<?php
/**
 * Copyright (c) 2014 Axel Helmert
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
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\tool;

use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\Console\ColorInterface;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;


class Module implements
    ConfigProviderInterface,
    ConsoleBannerProviderInterface
{
    /**
     * {@inheritdoc}
     * @see \Zend\ModuleManager\Feature\ConfigProviderInterface::getConfig()
     */
    public function getConfig()
    {
        return array(
            'console' => array(
                'router' => array(
                    'routes' => array(
                        'skeleton-generator' => array(
                            'type' => 'simple',
                            'options' => array(
                                'route' => '[--mainModuleName=] [-v]',
                                'defaults' => array(
                                    'controller' => __NAMESPACE__ . '\CreateSkeletonController',
                                    'mainModuleName' => 'application',
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     * @see \Zend\ModuleManager\Feature\ConsoleBannerProviderInterface::getConsoleBanner()
     */
    public function getConsoleBanner(ConsoleAdapterInterface $console)
    {
        $banner = $console->colorize('Application Skeleton Generator', ColorInterface::LIGHT_CYAN) . "\n\n"
                . 'This tool will create a application skeleton in the current directory.'
                . "\n\n";

        return $banner;
    }
}
