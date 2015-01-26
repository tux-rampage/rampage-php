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

namespace rampage\core;

use Zend\ServiceManager\ServiceManager as ZendServiceManager;

/**
 * Service manager
 */
class ServiceManager extends ZendServiceManager
{
    /**
     * @var array map of characters to be replaced through strtr
     */
    protected $canonicalNamesReplacements = array(
        '-' => '',
        '_' => '',
        ' ' => '',
        '\\' => '.',
        '/' => '.'
    );

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceManager::canonicalizeName()
     */
    protected function canonicalizeName($name)
    {
        $cName = parent::canonicalizeName($name);
        $cName = trim($cName, '.');

        return $cName;
    }

    /**
     * Custom setShared implementation.
     *
     * This does not check if a service can be instanciated by this service manager.
     * Definitions may be added at runtime so don't care about instanciability.
     *
     * @param string $name The service to mark as shared or not shared
     * @param bool $isShared Flag if the service should be shared or not
     * @return \rampage\core\ServiceManager
     */
    public function setShared($name, $isShared)
    {
        $cName = $this->canonicalizeName($name);
        $this->shared[$cName] = (bool)$isShared;

        return $this;
    }

    /**
     * Auto convert dottet class names to PHP class names
     *
     * @see \Zend\ServiceManager\ServiceManager::setInvokableClass()
     */
    public function setInvokableClass($name, $invokableClass, $shared = null)
    {
        $invokableClass = strtr($invokableClass, '.', '\\');
        return parent::setInvokableClass($name, $invokableClass, $shared);
    }
}
