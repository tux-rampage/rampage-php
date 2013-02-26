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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\db\platform;

/**
 * Fieldmapper
 */
class FieldMapper
{
    /**
     * Fields to attribute map
     *
     * @var string
     */
    protected $fieldToAttribute = array();

    /**
     * Attributes to field map
     *
     * @var array
     */
    protected $attributeToField = array();

    /**
     * Construct
     *
     * @param \Traversable|array $attributeMap
     */
    public function __construct($attributeMap = array())
    {
        if (!is_array($attributeMap) && !($attributeMap instanceof \Traversable)) {
            return;
        }

        foreach ($attributeMap as $attribute => $field) {
            $this->add($attribute, $field);
        }
    }

    /**
     * Add a mapping
     *
     * @param string $attribute
     * @param string $field
     */
    public function add($attribute, $field)
    {
        $attribute = $this->formatAttributeName($attribute);
        $field = $this->formatFieldname($field);

        $this->attributeToField[$attribute] = $field;
        $this->fieldToAttribute[$field] = $attribute;

        return $this;
    }

    /**
     * Format a fieldname
     *
     * @param string $field
     * @return string
     */
    protected function formatFieldname($field)
    {
        return strtolower($field);
    }

    /**
     * Format attribute name
     *
     * @param string $attribute
     * @return string
     */
    protected function formatAttributeName($attribute)
    {
        return strtolower($attribute);
    }

    /**
     * Map the given attribute to a field name
     *
     * @param string $attribute
     * @return string
     */
    public function mapAttribute($attribute)
    {
        $attribute = $this->formatAttributeName($attribute);
        if (!isset($this->attributeToField[$attribute])) {
            $this->add($attribute, $attribute);
        }

        return $this->attributeToField[$attribute];
    }

    /**
     * Map a database field to an attribute
     *
     * @param string $field
     */
    public function mapField($field)
    {
        $field = $this->formatFieldname($field);
        if (!isset($this->fieldToAttribute[$field])) {
            $this->add($field, $field);
        }

        return $this->fieldToAttribute[$field];
    }
}