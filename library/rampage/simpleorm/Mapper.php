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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm;

use Traversable;

/**
 * Field mapper class
 */
class Mapper
{
    private $attributesToFields = array();
    private $fieldsToAttributes = array();

    /**
     * Add mapping
     *
     * @param unknown $field
     * @param unknown $attribute
     * @return \rampage\simpleorm\Mapper
     */
    public function addMapping($field, $attribute)
    {
        $this->attributesToFields[$attribute] = $field;
        $this->fieldsToAttributes[$field] = $attribute;

        return $this;
    }

    /**
     * @param string $field
     */
    public function mapFieldname($field)
    {
        if (isset($this->fieldsToAttributes[$field])) {
            return $this->fieldsToAttributes[$field];
        }

        return $field;
    }

    /**
     * @param string $attribute
     * @return string
     */
    public function mapAttribute($attribute)
    {
        if (isset($this->attributesToFields[$attribute])) {
            return $this->attributesToFields[$attribute];
        }

        return $attribute;
    }

    /**
     * @param array $data
     * @param string $toDatabase
     * @return array
     */
    public function map($data, $toDatabase = false)
    {
        if (!is_array($data) && !($data instanceof Traversable)) {
            return false;
        }

        $result = array();
        foreach ($data as $key => $value) {
            $newKey = ($toDatabase)? $this->mapAttribute($key) : $this->mapFieldname($key);
            $result[$newKey] = $value;
        }

        return $result;
    }
}