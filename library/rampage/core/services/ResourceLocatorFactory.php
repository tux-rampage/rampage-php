<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2013 Axel Helmert
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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\services;

use rampage\core\resources\FileLocatorInterface;
use rampage\core\resources\FileLocator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory to create filelocator instances
 */
class ResourceLocatorFactory implements FactoryInterface
{
    /**
     * Add resources
     *
     * @param ServiceLocatorInterface $serviceManager
     * @param array|Traversable $config
     * @return self
     */
    protected function addResources(FileLocatorInterface $fileLocator, $config)
    {
        if (!is_array($config) || !($config instanceof \Traversable)) {
            return $this;
        }

        foreach ($config as $scope => $path) {
            $fileLocator->addLocation($scope, $path);
        }

        return $this;
    }

    /**
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $pathManager = $serviceLocator->get('rampage.PathManager');
        $fileLocator = new FileLocator($pathManager);

        if (!isset($config['rampage']['resources'])) {
            $this->addResources($fileLocator, $config['rampage']['resources']);
        }

        return $fileLocator;
    }


}