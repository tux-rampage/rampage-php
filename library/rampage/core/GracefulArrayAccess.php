<?php
/**
 * Copyright (c) 2014 Axel Helmert
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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core;

use ArrayAccess;
use UnexpectedValueException;


/**
 * Allows accessing values of an array (or an instance that implements ArrayAccess) gracefully.
 */
class GracefulArrayAccess
{
    /**
     * @var array
     */
    protected $container;

    /**
     * @param array|\ArrayAccess $container The container object/array
     * @throws UnexpectedValueException
     */
    public function __construct($container)
    {
        if (!is_array($container) && !($container instanceof ArrayAccess)) {
            throw new UnexpectedValueException('$container must be an array or implement ArrayAccess');
        }

        $this->container = $container;
    }

    /**
     * Check if the given key exists
     *
     * @param string|int $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->container[$key]);
    }

    /**
     * Returns the value for the given key or the provided default if the key does not exist.
     *
     * @param string|key $key
     * @param mixed $default
     */
    public function get($key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->container[$key];
    }
}
