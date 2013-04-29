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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm\metadata;

use rampage\simpleorm\exception;
use ArrayObject;

/**
 * Attribute collection
 */
class AttributeCollection extends ArrayObject
{
	/**
     * @see ArrayObject::__construct()
     */
    public function __construct($attributes)
    {
        parent::__construct(array());
        $this->exchangeArray($attributes);
    }

    /**
     * @see ArrayObject::append()
     */
    public function append($attribute)
    {
        if (!$attribute instanceof Attribute) {
            throw new exception\InvalidArgumentException('Invalid attribute instance');
        }

        $this->offsetSet($attribute->getName(), $attribute);
        return $this;
    }

    /**
     * Add an attribute
     *
     * @param array|Attribute $attribute
     * @return self
     */
    public function add($attribute)
    {
        if (is_array($attribute)) {
            $attribute = Attribute::factory($attribute);
        }

        $this->append($attribute);
        return $this;
    }

    /**
     * @see ArrayObject::exchangeArray()
     */
    public function exchangeArray(array $input)
    {
        parent::exchangeArray(array());

        foreach ($input as $value) {
            $this->add($value);
        }

        return $this;
    }

    /**
     * @see ArrayObject::offsetSet()
     */
    public function offsetSet($index, $attribute)
    {
        if (!$attribute instanceof Attribute) {
            throw new exception\InvalidArgumentException('Invalid attribute instance');
        }

        return parent::offsetSet($index, $attribute);
    }

    /**
     * @param string $field
     * @throws \rampage\simpleorm\exception\AttributeNotFoundException
     * @return \rampage\simpleorm\metadata\Attribute
     */
    public function getAttributeByField($field)
    {
        /* @var $attribute Attribute */
        foreach ($this as $attribute) {
            if ($attribute->getField() != $field) {
                continue;
            }

            return $attribute;
        }

        throw new exception\AttributeNotFoundException('No attribute for field: ' . $field);
    }
}
