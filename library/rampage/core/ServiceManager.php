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
use Zend\ServiceManager\Exception as svcexception;

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
     * Also check for class name in package format (foo.bar.BazClass) and add the
     * appropriate PHP name as well (foo\bar\Baz)
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
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceManager::setInvokableClass()
     */
    public function setInvokableClass($name, $invokableClass, $shared = null)
    {
        $invokableClass = strtr($invokableClass, '.', '\\');
        return parent::setInvokableClass($name, $invokableClass, $shared);
    }

    /**
     * Set an alias
     *
     * Do not change the class name, this would cause abstract factories to fail
     * When an alias is set to an explicit class name
     *
     * Note: We'll NOT force users to declare each alias as invokable.
     *
     * @see \Zend\ServiceManager\ServiceManager::setAlias()
     */
    public function setAlias($alias, $class)
    {
        $canonical = $this->canonicalizeName($alias);
        if ($alias == $class) {
            unset($this->aliases[$canonical]);
            return $this;
        }

        $this->aliases[$canonical] = $class;
        return $this;
    }

    /**
     * Check if a service name is shared
     *
     * @param string $name
     * @param string $requestedName
     * @return string
     */
    protected function isShared($name, $requestedName)
    {
        if (isset($this->shared[$name]) && ($this->shared[$name] === true)) {
            return true;
        }

        return ($this->shareByDefault() && !isset($this->shared[$name]));
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceManager::get()
     */
    public function get($name, $usePeeringServiceManagers = true)
    {
        $cName = $this->canonicalizeName($name);
        $cOrigName = false;
        $rName = $name;

        // shared alias?
        if (isset($this->instances[$cName])) {
            return $this->instances[$cName];
        }

        if ($this->hasAlias($cName)) {
            $cOrigName = $cName;
            $rOrigName = $rName;
            $stack = array(); // cycle check

            do {
                if (isset($stack[$cName])) {
                    throw new exception\CircularServiceReferenceException('Circular alias reference detected: ' . implode(' -> ', $stack));
                }

                $stack[$cName] = $cName;
                $rName = $this->aliases[$cName];
                $cName = $this->canonicalizeName($rName);
            } while ($this->hasAlias($cName));

            if (!$this->has(array($cName, $rName))) {
                throw new svcexception\ServiceNotFoundException(sprintf('An alias "%s" was requested but no service could be found.', $name));
            }
        }

        if (isset($this->instances[$cName])) {
            return $this->instances[$cName];
        }

        $instance = null;
        $retrieveFromPeeringManagerFirst = $this->retrieveFromPeeringManagerFirst();

        if ($usePeeringServiceManagers && $retrieveFromPeeringManagerFirst) {
            $instance = $this->retrieveFromPeeringManager($name);
        }

        if (!$instance) {
            if ($this->canCreate(array($cName, $rName))) {
                $instance = $this->create(array($cName, $rName));
            } elseif ($usePeeringServiceManagers && !$retrieveFromPeeringManagerFirst) {
                $instance = $this->retrieveFromPeeringManager($name);
            }
        }

        // Still no instance? raise an exception
        if (!$instance && !is_array($instance)) {
            throw new svcexception\ServiceNotFoundException(sprintf(
                '%s was unable to fetch or create an instance for %s',
                __METHOD__,
                $name
            ));
        }

        // Share the resolved name
        if ($this->isShared($cName, $rName)) {
            $this->instances[$cName] = $instance;
        }

        // The alias may not be shared, but the originally requested one might be
        if ($cOrigName && $this->isShared($cOrigName, $rOrigName)) {
            $this->instances[$cOrigName] = $instance;
        }

        return $instance;
    }
}
