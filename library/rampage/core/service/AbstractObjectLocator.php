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

namespace rampage\core\service;

use Zend\ServiceManager\ServiceLocatorInterface;
use rampage\core\ObjectManagerInterface;
use rampage\core\exception\RuntimeException;
use rampage\core\exception\InvalidServiceTypeException;

/**
 * Abstract object locator
 */
abstract class AbstractObjectLocator implements ServiceLocatorInterface
{
    /**
     * Object manager instance
     *
     * @var \rampage\core\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Service definition
     *
     * @var array
     */
    protected $invokables = array();

    /**
     * Flag if only defined services are available
     *
     * @var bool
     */
    protected $strict = false;

    /**
     * The required instance type
     *
     * Specify a class or inteface name.
     * If this property is not null, all instances must implement
     * or inherit from this type.
     *
     * @var string
     */
    protected $requiredInstanceType = null;

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
     * Object manager
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Object manager
     *
     * @return \rampage\core\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Canonicalize the service name
     *
     * @param string $name
     */
    protected function canonicalizeName($name)
    {
        $canonical = strtr(strtolower($name), $this->canonicalNamesReplacements);
        $canonical = trim($canonical, '.');

        return $canonical;
    }

    /**
     * Ensure a valid instance
     *
     * @param object $instance The instance to validate
     * @param string $name The requested service name
     * @throws \rampage\core\exception\InvalidServiceTypeException When the given instance doesn't pass validation
     * @return \rampage\core\service\AbstractObjectLocator $this (Fluent Interface)
     */
    protected function ensureValidInstance($instance, $name)
    {
        if ($this->requiredInstanceType === null) {
            return $this;
        }

        if (!$instance instanceof $this->requiredInstanceType) {
            throw new InvalidServiceTypeException(sprintf(
                'The requested service "%s" must implement "%s", but its type "%s" doesn\'t',
                $name, $this->requiredInstanceType,
                (is_object($instance))? get_class($instance) : gettype($instance)
            ));
        }

        return $this;
    }

    /**
     * Add a service definition
     *
     * @param string $name
     * @param string $class
     */
    public function setServiceClass($name, $class)
    {
        $name = $this->canonicalizeName($name);
        $this->invokables[$name] = $class;
    }

    /**
     * Create the requested service instance
     *
     * @param string $name
     * @param array $options
     * @return object
     */
    protected function create($name, array $options = array())
    {
        return $this->getObjectManager()->get($name, $options);
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceLocatorInterface::get()
     */
    public function get($name, array $options = array())
    {
        if (!$this->has($name)) {
            throw new RuntimeException('Failed to locate object: ' . $name);
        }

        $cName = $this->canonicalizeName($name);
        if (isset($this->invokables[$cName])) {
            $name = $this->invokables[$cName];
        }

        $instance = $this->create($name, $options);
        $this->ensureValidInstance($instance, $name);

        return $instance;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceLocatorInterface::has()
     */
    public function has($name)
    {
        $cName = $this->canonicalizeName($name);
        $available = isset($this->invokables[$cName]);

        if ($this->strict) {
            $available = $available || $this->getObjectManager()->has($name);
        }

        return $available;
    }
}