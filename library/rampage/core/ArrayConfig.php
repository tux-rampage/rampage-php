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

use Zend\Config\Config as BaseArrayConfig;

class ArrayConfig extends BaseArrayConfig
{
    /**
     * Define the section delimiter for get operations
     *
     * @var string
     */
    protected $sectionDelimiter = '.';

    /**
     * {@inheritdoc}
     * @see \Zend\Config\Config::__construct()
     */
    public function __construct($data = array(), $allowModifications = false)
    {
        if ($data instanceof \Traversable) {
            $data = iterator_to_array($data, true);
        }

        parent::__construct($data, $allowModifications);
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Config\Config::get()
     */
    public function get($name, $default = null)
    {
        if (strpos($name, $this->sectionDelimiter) === false) {
            return parent::get($name, $default);
        }

        list($section, $property) = explode($this->sectionDelimiter, $name, 2);
        $section = ($this->offsetExists($section))? $this->get($section) : null;

        if (!$section instanceof self) {
            return $default;
        }

        return $section->get($property, $default);
    }

    /**
     * Returns a section
     *
     * This will always return a ArrayConfig instance.
     * If the section does not exist, this will return an empty section
     *
     * @param string $section
     * @return self
     */
    public function getSection($section)
    {
        $section = $this->get($section);

        if (!$section instanceof self) {
            if (is_array($section) || ($section instanceof \Traversable)) {
                $section = new static($section);
            } else {
                $section = new static();
            }
        }

        return $section;
    }
}
