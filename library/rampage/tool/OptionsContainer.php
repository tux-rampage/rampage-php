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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\tool;

use ArrayObject;

class OptionsContainer extends ArrayObject
{
    /**
     * @var string
     */
    const OPTION_PUBLIC_DIR = 'public-directory';

    /**
     * @var array
     */
    protected $canonicalKeyReplacements = array(
        ' ' => '',
        '-' => '',
        '_' => '',
        '/' => '.',
    );

    /**
     * @see ArrayObject::__construct()
     */
    public function __construct(array $array = array())
    {
        parent::__construct(array());
        $this->exchangeArray($array);
    }

    /**
     * @param string $key
     * @return string
     */
    protected function canonicalizeKey($key)
    {
        $key = strtolower($key);
        $key = strtr($key, $this->canonicalKeyReplacements);

        return $key;
    }

    /**
     * @see ArrayObject::exchangeArray()
     */
    public function exchangeArray($input)
    {
        foreach ($input as $key => $value) {
            $key = $this->canonicalizeKey($key);
            $this->offsetSet($key, $value);
        }

        return $this;
    }

    /**
     * @see ArrayObject::offsetSet()
     */
    public function offsetSet($index, $newval)
    {
        return parent::offsetSet($this->canonicalizeKey($index), $newval);
    }

	/**
     * @see ArrayObject::offsetUnset()
     */
    public function offsetUnset($index)
    {
        return parent::offsetUnset($this->canonicalizeKey($index));
    }

    /**
     * @see ArrayObject::offsetExists()
     */
    public function offsetExists($index)
    {
        return parent::offsetExists($this->canonicalizeKey($index));
    }

    /**
     * @see ArrayObject::offsetGet()
     */
    public function offsetGet($index)
    {
        return parent::offsetGet($this->canonicalizeKey($index));
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return string|mixed
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
     * @return string
     */
    public function getPublicDirectory()
    {
        return (string)$this->get(self::OPTION_PUBLIC_DIR, 'public');
    }
}
