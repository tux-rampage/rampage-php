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

/**
 * Identifier
 */
class Identifier extends AttributeCollection
{
    /**
     * @param Entity $entity
     */
    public function __construct(Entity $entity)
    {
        parent::__construct(array());

        /* @var $attribute Attribute */
        foreach ($entity->getAttributes() as $attribute) {
            if (!$attribute->isIdentifier()) {
                continue;
            }

            $this->append($attribute);
        }
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return ($this->count() > 0);
    }

    /**
     * @return boolean
     */
    public function isMultiKey()
    {
        return ($this->count() > 1);
    }

    /**
     * @return \rampage\simpleorm\metadata\Attribute
     */
    public function getAttribute()
    {
        foreach ($this as $attribute) {
            return $attribute;
        }

        throw new exception\AttributeNotFoundException('No attribute defined for identifier');
    }
}
