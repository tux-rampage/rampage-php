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

namespace rampage\orm\entity\type;

use ArrayIterator;
use IteratorAggregate;

/**
 * Entity identifier
 *
 * @author helmert
 */
class Identifier implements IteratorAggregate
{
    /**
     * Attributes
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * All attribute names
     *
     * @var string
     */
    protected $attributeNames = array();

    /**
     * Construct
     *
     * @param EntityType $entityType
     */
    public function __construct(EntityType $entityType)
    {
        foreach ($entityType->getAttributes() as $attribute) {
            if (!$attribute->isIdentifier()) {
                continue;
            }

            $this->attributes[$attribute->getName()] = $attribute;
        }
    }

    /**
     * Check if this identifier is undefined
     *
     * @return boolean
     */
    public function isUndefined()
    {
        return (count($this->attributes) == 0);
    }

    /**
     * Check if the identifier is a generated value
     *
     * @return bool
     */
    protected function isGenerated()
    {
        if ($this->isUndefined() || $this->isMultiAttribute()) {
            return false;
        }

        return $this->getAttribute()->isGenerated();
    }

    /**
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new ArrayIterator($this->attributes);
    }

    /**
     * Multiple attribute
     */
    public function isMultiAttribute()
    {
        return (count($this->attributes) > 1);
    }

    /**
     * Returns the id attribute for single attribute ids
     *
     * @return \rampage\orm\entity\type\Attribute
     */
    public function getAttribute()
    {
        return $this->getIterator()->current();
    }

    /**
     * Returns all attribute names
     *
     * @return string
     */
    public function getAttributeNames()
    {
        return array_keys($this->attributes);
    }
}
