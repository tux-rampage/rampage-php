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

namespace rampage\core\service;

use rampage\core\exception;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use rampage\core\ObjectManagerInterface;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Factory for creating services by di container
 */
class CreateByDiFactory implements FactoryInterface
{
    /**
     * @var string
     */
    protected $class = null;

    /**
     * @param string $class
     */
    public function __construct($class)
    {
        if (!is_string($class) || !($class = trim($class))) {
            throw new exception\InvalidArgumentException('Class name must be a string and must not be empty.');
        }

        $this->class = $class;
    }

    /**
     * @param array $data
     * @return \rampage\core\service\CreateByDiFactory
     */
    public static function __set_state($data)
    {
        return new static($data['class']);
    }

    /**
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (!$this->class) {
            throw new exception\LogicException('No class name defined for service factory.');
        }

        if (($serviceLocator instanceof AbstractPluginManager) && $serviceLocator->getServiceLocator()) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        if (!$serviceLocator->has('ObjectManager')) {
            $class = strtr($this->class, '.', '\\');
            return new $class();
        }

        $objectManager = $serviceLocator->get('ObjectManager');
        if (!$objectManager instanceof ObjectManagerInterface) {
            throw new exception\RuntimeException('Could not find object manager to instanciate ' . $this->class);
        }

        return $objectManager->newInstance($this->class);
    }
}