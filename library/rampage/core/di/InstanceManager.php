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

namespace rampage\core\di;

use Zend\Di\InstanceManager as DefaultInstanceManager;
use Zend\Di\Exception;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * Instance manager
 */
class InstanceManager extends DefaultInstanceManager implements ServiceManagerAwareInterface, ServiceEnabledInterface
{
    /**
     * Service Manager
     *
     * @var \Zend\ServiceManager\ServiceManager
     */
    private $serviceManager = null;


    /**
     * Constructor
     *
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager = null)
    {
        if ($serviceManager) {
            $this->setServiceManager($serviceManager);
        }
    }


    /**
     * Set the current service manager
     *
     * @param ServiceManager $manager
     */
    public function setServiceManager(ServiceManager $manager)
    {
        $this->serviceManager = $manager;
        return $this;
    }

    /**
     * Check if service exists
     *
     * @param string $name
     */
    public function hasService($name)
    {
        if (!$this->serviceManager) {
            return false;
        }

        return $this->serviceManager->has($name, false);
    }

    /**
     * Returns a specific service instance
     *
     * @param string $name
     * @return object
     */
    public function getService($name)
    {
        if (!$this->serviceManager) {
            throw new Exception\UndefinedReferenceException('Cannot get service without an service manager instance!');
        }

        return $this->serviceManager->get($name);
    }

    /**
     * @see \Zend\Di\InstanceManager::getSharedInstance()
     */
    public function getSharedInstance($classOrAlias)
    {
        if ($this->hasService($classOrAlias)) {
            return $this->getService($classOrAlias);
        }

        return parent::getSharedInstance($classOrAlias);
    }

	/**
     * @see \Zend\Di\InstanceManager::hasSharedInstance()
     */
    public function hasSharedInstance($classOrAlias)
    {
        if ($this->hasService($classOrAlias)) {
            return true;
        }

        return parent::hasSharedInstance($classOrAlias);
    }
}
