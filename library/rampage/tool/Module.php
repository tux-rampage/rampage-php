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
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;


class Module implements
    ConfigProviderInterface,
    ConsoleBannerProviderInterface,
    ConsoleUsageProviderInterface
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
                        'usage' => array(
                            'type' => 'catchall',
                            'options' => array(
                                'route' => '',
                                'defaults' => array(
                                    'controller' => __NAMESPACE__ . '\CreateSkeletonController',
                                    'action' => 'usage',
                                )
                            )
                        ),
                        'skeleton-generator' => array(
                            'type' => 'simple',
                            'options' => array(
                                'route' => 'create [-v] <mainModuleName>',
                                'defaults' => array(
                                    'controller' => __NAMESPACE__ . '\CreateSkeletonController',
                                    'mainModuleName' => 'application',
                                    'action' => 'create',
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * @return boolean
     */
    public function getConsoleLabel()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\ModuleManager\Feature\ConsoleBannerProviderInterface::getConsoleBanner()
     */
    public function getConsoleBanner(ConsoleAdapterInterface $console)
    {
        $banner = $console->colorize("\n" . 'Application Skeleton Generator', ColorInterface::LIGHT_CYAN) . "\n\n"
                . 'This tool will create a application skeleton in the current directory.';

        return $banner;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\ModuleManager\Feature\ConsoleUsageProviderInterface::getConsoleUsage()
     */
    public function getConsoleUsage(ConsoleAdapterInterface $console)
    {
        return array(
            $console->colorize('Usage:', ColorInterface::YELLOW),
            'create <applicationModuleName>' => 'Create a project skeleton',
            $console->colorize('Options:', ColorInterface::YELLOW),
            array('applicationModuleName', "The name of the application module.\nUse a dot for namespaces (e.g.: acme.app).\n"),
        );
    }
}
