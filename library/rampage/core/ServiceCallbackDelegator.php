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

namespace rampage\core;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServiceCallbackDelegator implements ServiceLocatorAwareInterface
{
    /**
     * @var string
     */
    protected $serviceName = null;

    /**
     * @var string
     */
    protected $method = null;

    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator = null;

    /**
     * @var callable
     */
    private $callback = null;

    /**
     * @param string $serviceName
     * @param string $method
     */
    public function __construct($serviceName, $method = null)
    {
        $this->serviceName = $serviceName;
        $this->method = $method;
    }
	/**
     * {@inheritdoc}
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::getServiceLocator()
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

	/**
     * {@inheritdoc}
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * @throws exception\LogicException
     * @return \rampage\core\callable
     */
    public function createCallback()
    {
        if ($this->callback !== null) {
            return $this->callback;
        }

        if (!$this->serviceLocator) {
            throw new exception\LogicException('Cannot create service without service locator.');
        }

        $service = $this->getServiceLocator()->get($this->serviceName);
        $this->callback = ($this->method)? array($service, $this->method) : $service;

        return $this->callback;
    }

    /**
     * @return mixed
     */
    public function __invoke()
    {
        $args = func_get_args();
        return call_user_func_array($this->createCallback(), $args);
    }
}
