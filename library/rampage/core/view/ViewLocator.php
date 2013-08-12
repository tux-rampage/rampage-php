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

namespace rampage\core\view;

use rampage\core\AbstractPluginManager;
use rampage\core\di\DIContainerAware;
use Zend\Di\Di as DIContainer;
use Zend\ServiceManager\ConfigInterface;

/**
 * Returns the view locator
 */
class ViewLocator extends AbstractPluginManager implements DIContainerAware
{
    /**
     * @var \Zend\Di\Di
     */
    private $di = null;

    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::__construct()
     */
    public function __construct(ConfigInterface $configuration = null)
    {
        parent::__construct($configuration);

        $this->autoAddInvokableClass = true;
        $this->shareByDefault = false;

    }

    /**
     * @see \rampage\core\di\DIContainerAware::setDIContainer()
     */
    public function setDIContainer(DIContainer $container)
    {
        $this->di = $container;
    }

    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::createFromInvokable()
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        if (!$this->di) {
            return parent::createFromInvokable($canonicalName, $requestedName);
        }

        $class = $this->invokableClasses[$canonicalName];
        $params = (is_array($this->creationOptions))? $this->creationOptions : array();

        return $this->di->newInstance($class, $params, false);
    }

	/**
     * @see \Zend\ServiceManager\AbstractPluginManager::validatePlugin()
     */
    public function validatePlugin($plugin)
    {
        return ($plugin instanceof RenderableInterface);
    }
}
