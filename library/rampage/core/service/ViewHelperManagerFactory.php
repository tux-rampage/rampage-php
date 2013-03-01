<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2012 Axel Helmert
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
 * @category  library
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\service;

use rampage\core\view\helper\PluginManager;
use Zend\Mvc\Service\ViewHelperManagerFactory as DefaultViewHelperManagerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Console\Console;
use Zend\Mvc\Router\RouteMatch;

/**
 * View halper manager factory
 */
class ViewHelperManagerFactory extends DefaultViewHelperManagerFactory
{
    /**
     * Plugin manager class
     */
    const PLUGIN_MANAGER_CLASS = 'rampage\core\view\helper\PluginManager';

    /**
     * (non-PHPdoc)
     * @see \Zend\Mvc\Service\ViewHelperManagerFactory::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $instance = parent::createService($serviceLocator);

        // Configure URL view helper with router
        $instance->setFactory('url', function ($sm) use ($serviceLocator) {
            $helper = $serviceLocator->get('objectmanager')->get('rampage.core.view.helper.Url');
            $router = Console::isConsole() ? 'HttpRouter' : 'Router';
            $helper->setRouter($serviceLocator->get($router));

            $match = $serviceLocator->get('application')
                ->getMvcEvent()
                ->getRouteMatch();

            if ($match instanceof RouteMatch) {
                $helper->setRouteMatch($match);
            }

            return $helper;
        });

        if ($instance instanceof PluginManager) {
            $instance->setObjectManager($serviceLocator->get('objectmanager'));
        }

        return $instance;
    }
}