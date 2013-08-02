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

use rampage\core\exception;
use rampage\core\di\DIContainerAware;

use Zend\Di\Di;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creating services by di container
 */
class DIServiceFactory implements FactoryInterface, DIContainerAware
{
    /**
     * @var string
     */
    protected $class = null;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var string
     */
    private $di = null;

    /**
     * @param string $class
     */
    public function __construct($class, array $options = array())
    {
        if (!is_string($class) || !($class = trim($class))) {
            throw new exception\InvalidArgumentException('Class name must be a string and must not be empty.');
        }

        $this->options = $options;
        $this->class = strtr(trim($class), '.', '\\');
    }

    /**
     * @see \rampage\core\di\DIContainerAware::setDIContainer()
     */
    public function setDIContainer(Di $container)
    {
        $this->di = $container;
        return $this;
    }

    /**
     * Provide serializable members for var_export
     * @return string[]
     */
    public function __sleep()
    {
        return array('class');
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
     * @param ServiceLocatorInterface $serviceLocator
     * @throws exception\DependencyException
     * @return \Zend\Di\Di
     */
    protected function fetchDIContainerFromServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $di = $serviceLocator->get('di');
        if (!$di instanceof Di) {
            throw new exception\DependencyException('Could not fetch DI container from service locator');
        }

        return $di;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Zend\Di\Di
     */
    protected function getDiContainer(ServiceLocatorInterface $serviceLocator)
    {
        if (!$this->di) {
            $this->setDIContainer($this->fetchDIContainerFromServiceLocator($serviceLocator));
        }

        return $this->di;
    }

    /**
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (!$this->class) {
            throw new exception\LogicException('No class name defined for service factory.');
        }

        return $this->getDiContainer($serviceLocator)->newInstance($this->class, $this->options, false);
    }
}