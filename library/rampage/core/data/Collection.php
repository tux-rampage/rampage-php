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

use IteratorAggregate;
use Countable;
use ArrayIterator;

/**
 * Class for data collections
 */
class Collection implements IteratorAggregate, Countable
{
    /**
     * All items in this collection
     *
     * @var array
     */
    protected $items = array();

    /**
     * collection size
     *
     * @var int
     */
    private $size = null;

    /**
     * set collection size
     *
     * @param int $size
     */
    public function setSize($size)
    {
        if ($size !== null) {
            $size = (int)$size;
            if ($size < 0) {
                $size = 0;
            }
        }

        $this->size = $size;
        return $this;
    }

    /**
     * Return collection size
     *
     * @return int
     */
    public function getSize()
    {
        if ($this->size === null) {
            return $this->count();
        }

        return $this->size;
    }

    /**
     * Add a Item to this collection
     *
     * @param object $item
     */
    public function addItem($item)
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Implementation of IteratorAggregate.getIterator()
     *
     * @see IteratorAggregate.getIterator()
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Implementation of Countable.count()
     * @see Countable.count()
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Clears this collection
     */
    public function clear()
    {
        $this->items = array();
        return $this;
    }

    /**
     * Remove the given item from this collection
     *
     * @param mixed $remove
     */
    public function removeItem($remove)
    {
        foreach ($this->items as $index => $item) {
            if ($item !== $remove) {
                continue;
            }

            unset($this->items[$index]);
        }

        return $this;
    }

    /**
     * Removes the given item by it's key
     *
     * @param int|string $key
     */
    public function removeItemByKey($key)
    {
        if (isset($this->items[$key])) {
            unset($this->items[$key]);
        }

        return $this;
    }

    /**
     * Reset this collection
     */
    public function reset()
    {
        $this->clear()->setSize(null);
        return $this;
    }
}