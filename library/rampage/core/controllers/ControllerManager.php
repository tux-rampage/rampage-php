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

namespace rampage\core\controllers;

use rampage\core\di\DIContainerAware;
use Zend\Mvc\Controller\ControllerManager as ZendControllerManager;
use Zend\Di\Di;

/**
 * Controller Manager
 */
class ControllerManager extends ZendControllerManager implements DIContainerAware
{
    /**
     * @var Di
     */
    private $di = null;

    /**
     * @var bool
     */
    protected $autoAddInvokableClass = true;

    /**
     * @see \rampage\core\di\DIContainerAware::setDIContainer()
     */
    public function setDIContainer(Di $container)
    {
        $this->di = $container;
        return $this;
    }

    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::createFromInvokable()
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        if (!$this->di) {
            return parent::createFromInvokable($canonicalName, $requestedName);
        }

        $invokable = $this->invokableClasses[$canonicalName];
        return $this->di->newInstance($invokable, $this->creationOptions, false);
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Mvc\Controller\ControllerManager::has()
     */
    public function has($name, $checkAbstractFactories = true, $usePeeringServiceManagers = false)
    {
        if (parent::has($name, $checkAbstractFactories, $usePeeringServiceManagers)) {
            return true;
        }

        if (is_array($name)) {
            return false;
        }

        $class = strtr($name, '.', '\\');
        if ($this->autoAddInvokableClass && class_exists($class)) {
            $this->setInvokableClass($name, $class);
            return true;
        }

        return false;
    }
}
