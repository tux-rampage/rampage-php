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

use rampage\core\ServiceManager;
use rampage\core\ObjectManagerInterface;

/**
 * Returns the view locator
 */
class ViewLocator extends ServiceManager
{
	/**
     * (non-PHPdoc)
     * @see \rampage\core\ServiceManager::__construct()
     */
    public function __construct(ObjectManagerInterface $parent)
    {
        $this->addPeeringServiceManager($parent);
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceManager::retrieveFromPeeringManagerFirst()
     */
    public function retrieveFromPeeringManagerFirst()
    {
        return false;
    }

    /**
     * @see \Zend\ServiceManager\ServiceManager::shareByDefault()
     */
    public function shareByDefault()
    {
        return false;
    }


	/**
     * Retrieve a view instance
     *
     * @param string $name Name of the view (class name)
     * @param bool $usePeeringServiceManagers Ignored - peering service managers will always be used
     * @return \rampage\core\view\View
     */
    public function get($name, $usePeeringServiceManagers = true)
    {
        return parent::get($name, true);
    }
}