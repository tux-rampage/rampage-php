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

namespace rampage\core\view;

use ArrayObject;

/**
 * Data container
 */
class DataContainer extends ArrayObject
{
    /**
     * @param array $array
     */
    public function __construct(array $array = array())
    {
        parent::__construct($array);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (!$this->offsetExists($key)) {
            return $default;
        }

        return $this->offsetGet($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value)
    {
        $this->offsetSet($key, $value);
        return $this;
    }

    /**
     * @see ArrayObject::offsetUnset()
     * @return self
     */
    public function offsetUnset($key)
    {
        parent::offsetUnset($key);
        return $this;
    }
}