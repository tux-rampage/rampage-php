<?php
/**
 * This is part of @application_name@
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
 * @package   @package_name@
 * @author    Axel Helmert
 * @copyright Copyright (c) 2012 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\resource;

use rampage\core\resource\Theme;
use Zend\EventManager\Event;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Bootstrap lister
 */
class BootstrapListener
{
    /**
     * Add config themes
     *
     * @param ServiceLocatorInterface $serviceManager
     * @param string $config
     * @return \rampage\core\design\BootstrapListener
     */
    public function addThemes(ServiceLocatorInterface $serviceManager, $config)
    {
        $theme = $serviceManager->get('rampage.Theme');
        if (!is_array($config) || !($theme instanceof Theme)) {
            return $this;
        }

        foreach ($config as $name => $path) {
            $theme->addLocation($name, $path);
        }

        return $this;
    }

    /**
     * Add resources
     *
     * @param ServiceLocatorInterface $serviceManager
     * @param string $config
     */
    public function addResources(ServiceLocatorInterface $serviceManager, $config)
    {
        $locator = $serviceManager->get('rampage.resource.FileLocator');
        if (!is_array($config) || !($locator instanceof FileLocator)) {
            return $this;
        }

        foreach ($config as $scope => $path) {
            $locator->addLocation($scope, $path);
        }

        return $this;
    }

    /**
     * Bootstrap listener
     *
     * @param Event $event
     */
    public function __invoke(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();
        $config = $serviceManager->get('config');

        if (isset($config['rampage']['themes'])) {
            $this->addThemes($serviceManager, $config['rampage']['themes']);
        }

        if (isset($config['rampage']['resources'])) {
            $this->addResources($serviceManager, $config['rampage']['resources']);
        }
    }
}