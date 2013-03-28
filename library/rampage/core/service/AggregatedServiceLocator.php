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

use rampage\core\ServiceManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Service locator for creating aggregated services
 */
class AggregatedServiceLocator extends ServiceManager
{
    /**
     * Parent locator
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $parent = null;

    /**
     * construct
     *
     * @param ServiceLocatorInterface $parent
     */
    public function __construct(ServiceLocatorInterface $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Parent service locator
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected function getParentLocator()
    {
        return $this->parent;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\ServiceManager::get()
     */
    public function get($name, $usePeeringServiceManagers = true)
    {
        if (!$this->has($name)) {
            return false;
        }

        list($manager, $service) = explode('://', $name, 2);
        return $this->getParentLocator()->get($manager)->get($service);
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceManager::has()
     */
    public function has($name, $checkAbstractFactories = true, $usePeeringServiceManagers = true)
    {
        if (strpos($name, '://') === false) {
            return false;
        }

        list($manager, $service) = explode('://', $name, 2);
        if (!$manager || !$service) {
            return false;
        }

        $parent = $this->getParentLocator();
        $services = ($parent->has($manager, false, false))? $parent->get($manager) : false;
        return (($services instanceof ServiceLocatorInterface)
            && $services->has($service));
    }
}