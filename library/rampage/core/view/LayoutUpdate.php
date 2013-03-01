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

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use rampage\core\xml\SimpleXmlElement;

/**
 * Layout update handling
 */
class LayoutUpdate implements IteratorAggregate
{
    /**
     * Handles to apply
     *
     * @var array
     */
    private $handles = array();

    /**
     * Applied handles
     *
     * @var array
     */
    private $collectedHandles = array();

    /**
     * Config xml
     *
     * @var \rampage\core\view\LayoutConfig
     */
    private $config = null;

    /**
     * Constructor
     *
     * @param LayoutConfig $config
     */
    public function __construct(LayoutConfig $config)
    {
        $this->setConfig($config);;
    }

    /**
     * Reset layout updates
     */
    public function clear()
    {
        $this->handles = array();
        return $this;
    }

    /**
     * Set layout config
     *
     * @param \rampage\core\view\LayoutConfig $config
     */
    public function setConfig(LayoutConfig $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get the current layout config
     *
     * @return \rampage\core\view\LayoutConfig
     */
    public function getConfig()
    {
        return $this->config;
    }


    /**
     * Merge handles to the given XML
     *
     * @param array $handles
     * @param array $stack
     * @return array
     */
    protected function fetchHandles(array $handles, array &$stack = array())
    {
        foreach ($handles as $name) {
            if (isset($stack[$name])) {
                continue;
            }

            $current = $this->getConfig()->getHandle($name);
            if (!$current instanceof SimpleXmlElement) {
                continue;
            }

            $stack[$name] = true;
            $updates = array();

            foreach ($current->xpath('./update') as $update) {
                $updatename = (string)$update['handle'];
                $updates[$updatename] = $updatename;
            }

            if (!empty($updates)) {
                $this->fetchHandles($updates, $stack);
            }

            $this->collectedHandles[$name] = $current;
        }

        return $this->collectedHandles;
    }

    /**
     * Collect layout nodes
     *
     * @return array
     */
    public function collectNodes()
    {
        $this->collectedHandles = array();
        return $this->fetchHandles($this->handles);
    }

    /**
     * Prepend handles
     *
     * @param string|array|Traversable $name
     * @return \rampage\core\view\LayoutUpdate
     */
    public function prepend($name)
    {
        if (is_array($name) || ($name instanceof Traversable)) {
            foreach ($name as $itemName) {
                $this->prepend($itemName);
            }

            return $this;
        }

        $this->handles = array($name => $name) + $this->handles;
        return $this;
    }

    /**
     * Add layout handle
     *
     * @param string $name
     * @return \rampage\core\view\LayoutUpdate
     */
    public function add($name)
    {
        if (is_array($name) || ($name instanceof Traversable)) {
            foreach ($name as $itemName) {
                $this->add($itemName);
            }

            return $this;
        }

        $this->handles[$name] = $name;
        return $this;
    }

    /**
     * Remove updates
     *
     * @param string $name
     * @return \rampage\core\view\LayoutUpdate
     */
    public function remove($name)
    {
        unset($this->handles[$name]);
        return $this;
    }

    /**
     * IteratorAggregate implementation
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->handles);
    }

    /**
     * Returns all current handles
     *
     * @return array
     */
    public function getHandles()
    {
        return $this->handles;
    }
}