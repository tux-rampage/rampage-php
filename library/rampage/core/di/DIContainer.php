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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\di;

use Zend\Di\Di as DefaultDIContainer;
use Zend\Di\InstanceManager as DefaultInstanceManager;
use Zend\Di\DefinitionList;
use Zend\Di\Config;

/**
 * Dependency injector
 *
 * @property \rampage\core\di\InstanceManager $instanceManager
 */
class DIContainer extends DefaultDIContainer
{
    /**
     * @see \Zend\Di\Di::__construct()
     */
    public function __construct(DefinitionList $definitions = null, DefaultInstanceManager $instanceManager = null, Config $config = null)
    {
        if (!$instanceManager) {
            $instanceManager = new InstanceManager();
        }

        parent::__construct($definitions, $instanceManager, $config);
    }

    /**
     * Returns the instance manager (consistency method)
     *
     * @return \rampage\core\di\InstanceManager
     */
    public function getInstanceManager()
    {
        return $this->instanceManager;
    }

    /**
     * Format class name
     *
     * @param string $name
     */
    protected function formatClassName($name)
    {
        $class = trim(strtr($name, '.', '\\'), '\\');
        return $class;
    }

    /**
     * @inheritdoc
     * @see \Zend\Di\Di::get()
     */
    public function get($name, array $params = array())
    {
        $class = $this->formatClassName($name);
        return parent::get($class, $params);
    }

    /**
     * @inheritdoc
     * @see \Zend\Di\Di::newInstance()
     */
    public function newInstance($name, array $params = array(), $isShared = false)
    {
        $class = $this->formatClassName($name);
        return parent::newInstance($class, $params, $isShared);
    }
}
