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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\services;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Abstract factory for creating services
 */
class DIAbstractServiceFactory extends DIServiceFactory implements AbstractFactoryInterface
{
    /**
     * Service name regex
     */
    const SERVICE_NAME_REGEX = '~^[a-z_][a-z0-9_]*([\\\\.][a-z_][a-z0-9_]*)*$~i';

    /**
     * @see \rampage\core\services\DIServiceFactory::__construct()
     */
    public function __construct()
    {
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\AbstractFactoryInterface::canCreateServiceWithName()
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        try {
            $di = $this->getDiContainer($serviceLocator);
        } catch (\Exception $e) {
            return false;
        }

        if (!preg_match(self::SERVICE_NAME_REGEX, $requestedName)) {
            return false;
        }

        $class = str_replace('.', '\\', $requestedName);
        if (class_exists($class) || $di->instanceManager()->hasAlias($class)) {
            return true;
        }

        return false;
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\AbstractFactoryInterface::createServiceWithName()
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (!$this->canCreateServiceWithName($serviceLocator, $name, $requestedName)) {
            return false;
        }

        $instance = $this->getDiContainer($serviceLocator)->newInstance($requestedName, array(), false);
        return $instance;
    }
}
