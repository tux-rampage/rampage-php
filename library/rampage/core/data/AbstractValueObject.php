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

namespace rampage\core\data;

use rampage\core\Utils;
use rampage\core\exception;
use Traversable;
use ArrayObject;

/**
 * Data object
 */
class AbstractValueObject implements ArrayExchangeInterface
{
    /**
     * Associative array/ArrayObject containing data for this object
     *
     * @var ArrayObject|array
     */
    protected $data = array();

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct($data = null)
    {
        if (is_array($data) || ($data instanceof Traversable)) {
            $this->populate($data);
        }
    }

    /**
     * Camelize the given name
     *
     * @param string $name
     * @return string
     */
    protected function underscore($name)
    {
        return Utils::underscore($name);
    }

    /**
     * Internal get
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get($key, $default = null)
    {
        $value = isset($this->data[$key])? $this->data[$key] : $default;
        return $value;
    }

    /**
     * Internal setter
     *
     * @param string $name
     * @param mixed $value
     * @return \rampage\core\data\Object $this
     */
    protected function set($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * Internal field check
     *
     * @param string $field
     * @return bool
     */
    protected function has($field)
    {
        return isset($this->data[$field]);
    }

    /**
     * Unset a data member
     *
     * @param string $field
     * @return Rampage_Data_Object $this
     */
    protected function remove($field)
    {
        if ($this->has($field)) {
            unset($this->data[$field]);
        }

        return $this;
    }

    /**
     * Clear data for this object
     */
    protected function clear()
    {
        if ($this->data instanceof \ArrayObject) {
            $this->data->exchangeArray(array());
            return $this;
        }

        $this->data = array();
        return $this;
    }

    /**
     * Reset this object to a clean state
     */
    public function reset()
    {
        $this->clear();
        return $this;
    }

    /**
     * Add data to this object
     *
     * @param \rampage\core\data\ArrayExchangeInterface|array|Traversable $data
     * @param bool $overwrite
     */
    public function add($data, $overwrite = true)
    {
        if ($data instanceof ArrayExchangeInterface) {
            $data = $data->getArrayCopy();
        }

        if (!is_array($data) && (!$data instanceof Traversable)) {
            throw new exception\InvalidArgumentException('$data must be an array or implement Traversable or ArrayExchangeInterface');
        }

        foreach ($data as $key => $value) {
            if ($overwrite || !$this->has($key)) {
                $this->set($key, $value);
            }
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\data\ArrayExchangeInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        if ($this->data instanceof ArrayObject) {
            return $this->data->getArrayCopy();
        }

        return $this->data;
    }

    /**
     * Exchange array
     *
     * This method is to avoid Zend\Stdlib\Hydrator\ArraySerializable is using __call()
     *
     * @param array $data
     * @return \rampage\core\data\Object
     */
    public function exchangeArray($data)
    {
        return $this->populate($data);
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\data\ArrayExchangeInterface::populate()
     */
    public function populate($data)
    {
        $this->clear()->add($data);
        return $this;
    }
}