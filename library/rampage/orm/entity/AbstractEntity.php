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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\entity;

use rampage\core\data\Object;
use rampage\orm\ValueObject;
use rampage\orm\entity\lazy\EntityInterface as LazyEntityInterface;
use rampage\orm\exception\RuntimeException;


/**
 * Abstract entity class
 */
abstract class AbstractEntity extends ValueObject implements EntityInterface, LazyEntityInterface
{
    /**
     * Lazy attributes
     *
     * @var array
     */
    protected $lazyAttributes = array();

    /**
     * (non-PHPdoc)
     * @see \rampage\core\data\Object::clear()
     */
    protected function clear()
    {
        $this->lazyAttributes = array();
        return parent::clear();
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\data\Object::get()
     */
    protected function get($key, $default = null)
    {
        if (!$this->has($key) && isset($this->lazyAttributes[$key]) && is_callable($this->lazyAttributes[$key])) {
            $value = call_user_func($this->lazyAttributes[$key], $key, $this);
            $this->set($key, $value);
        }

        return parent::get($key, $default);
    }

    /**
     * Lazy load a reference into the given property
     *
     * In order to work, the property must either be public or protected.
     * For private properties you must omit the $property parameteer and handle
     * the assignment by yourself.
     *
     * @param string $name
     * @param string $property
     * @param string $requiredClass
     */
    protected function lazyLoadReference($name, $property = null, $requiredClass = null)
    {
        if ($property && ($this->$property !== null)) {
            return $this->$property;
        }

        if (!isset($this->lazyAttributes[$name]) || !is_callable($this->lazyAttributes[$name])) {
            return null;
        }

        $object = call_user_func($this->lazyAttributes[$name], $name);
        if (($requiredClass) && !($object instanceof $requiredClass)) {
            throw new RuntimeException('The reference must be an instance of ' . $requiredClass);
        }

        if ($property) {
            $this->$property = $object;
        }

        return $object;
    }

    /**
     * @see \rampage\orm\entity\lazy\EntityInterface::addLazyAttribute()
     */
    public function addLazyAttribute($name, $delegate)
    {
        $this->lazyAttributes[$name] = $delegate;
        return $this;
    }
}