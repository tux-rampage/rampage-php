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
use rampage\core\exception\RuntimeException;
use rampage\core\ObjectManagerInterface;

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
        $canonical = trim($name, '.');

        return $canonical;
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
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceLocatorInterface::get()
     */
    public function get($name, array $options = array())
    {
        if (!$this->has($name)) {
            throw new RuntimeException('Failed to locate object: ' . $name);
        }

        $name = $this->canonicalizeName($name);
        if (isset($this->invokables[$name])) {
            $name = $this->invokables[$name];
        }

        return $this->getObjectManager()->get($name, $options);
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