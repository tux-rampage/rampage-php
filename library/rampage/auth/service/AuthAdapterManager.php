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

namespace rampage\auth\service;

use rampage\core\ObjectManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use rampage\auth\exception\InvalidArgumentException;

/**
 * Auth service manager
 */
class AuthAdapterManager implements ServiceLocatorInterface
{
    /**
     * Object manager
     *
     * @var \rampage\core\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Registered auth services
     *
     * @var array
     */
    protected $types = array();

    /**
     * Available adapters
     *
     * @var array
     */
    private $available = null;

    /**
     * Canonical name cache
     *
     * @var array
     */
    protected $canonicalNameCache = array();

    /**
     * @var array map of characters to be replaced through strtr
     */
    protected $canonicalNamesReplacements = array(
        '-' => '',
        '_' => '',
        ' ' => '',
        '\\' => '.',
        '/' => ''
    );

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceManager::__construct()
     */
    public function __construct(ObjectManagerInterface $manager, AdapterConfigInterface $config)
    {
        $this->objectManager = $manager;
        $config->configure($this);
    }

    /**
     * Canonicalize name
     *
     * @param string $name
     * @return string
     */
    protected function canonicalizeName($name)
    {
        if (isset($this->canonicalNameCache[$name])) {
            return $this->canonicalNameCache[$name];
        }

        $canonical = strtolower(strtr($name, $this->canonicalNamesReplacements));
        $this->canonicalNameCache[$name] = $canonical;

        return $canonical;
    }

    /**
     * Get the object manager instance
     *
     * @return \rampage\core\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Add an authentication adapter type
     *
     * @param string $type
     * @param string $class
     */
    public function addType($type, $class)
    {
        $type = $this->canonicalizeName($type);
        $this->types[$type] = $class;
    }

    /**
     * Returns all available adapters
     */
    public function getAvailableAdapters($reload = false)
    {
        if (!$reload && ($this->available !== null)) {
            $this->available;
        }

        $this->available = array();
        foreach ($this->types as $type => $class) {
            $this->available[$type] = $this->get($type);
        }

        return $this->available;
    }

    /**
     * Retrieve a registered instance
     *
     * @param  string  $name
     * @throws Exception\ServiceNotFoundException
     * @return object
     */
    public function get($name)
    {
        $name = $this->canonicalizeName($name);
        if (!isset($this->types[$name])) {
            throw new InvalidArgumentException('No such auth service: ' . $name);
        }

        $class = $this->types[$name];
        $instance = $this->getObjectManager()->get($class);

        return $instance;
    }

    /**
     * Check for a registered instance
     *
     * @param  string|array  $name
     * @return bool
    */
    public function has($name)
    {
        $name = $this->canonicalizeName($name);
        return isset($this->types[$name]);
    }
}
