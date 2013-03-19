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

use rampage\orm\exception\InvalidArgumentException;

/**
 * Reference definition
 */
class Reference
{
    const TYPE_MULTIPLE = 'multiple';
    const TYPE_SINGLE = 'single';

    /**
     * Referenced attributes
     *
     * @var array
     */
    private $attributes = array();

    /**
     * Referenced entity names
     *
     * @var string
     */
    private $referencedEntity = null;

    /**
     * Load lazy if possible
     *
     * @var bool
     */
    private $lazy = true;

    /**
     * Reference type
     *
     * @var string
     */
    private $type = self::TYPE_MULTIPLE;

    /**
     * Property to load into
     *
     * @var string|null
     */
    private $property = null;

    public function __construct($referencedEntity, array $attributeReferences = array())
    {
        $referencedEntity = (string)$referencedEntity;
        if ($referencedEntity == '') {
            throw new InvalidArgumentException('The referenced entity name must not be empty.');
        }

        foreach ($attributeReferences as $reference) {
            $this->addAttributeReference($reference);
        }

        $this->referencedEntity = $referencedEntity;
    }

    /**
     * Check for attribute references
     *
     * @return boolean
     */
    public function hasAttributeReferences()
    {
        return (count($this->attributes) > 0);
    }

    /**
     * Add an attribute reference
     *
     * @param AttributeReference|array $reference
     */
    public function addAttributeReference($reference)
    {
        if (is_array($reference)) {
            @list($attribute, $referencedAttribute) = $reference;
            if (count($reference) > 2) {
                $reference = new AttributeReference($attribute, $referencedAttribute, $reference[3]);
            } else {
                $reference = new AttributeReference($attribute, $referencedAttribute);
            }
        }

        if (!$reference instanceof AttributeReference) {
            throw new InvalidArgumentException(sprintf(
                'Attribute reference must be an array or an instance of rampage.orm.entity.type.AttributeReference, %s given',
                is_object($reference)? strtr(get_class($reference), '\\', '.') : gettype($reference)
            ));
        }

        $this->attributes[] = $reference;
    }

    /**
     * Returns reference attributes
     *
     * The local attribute name as key and the referenced attribute name as value
     *
     * @return \rampage\orm\entity\type\AttributeReference[]
     */
    public function getAttributeReferences()
    {
        return $this->attributes;
    }

    /**
     * Referenced entity name
     *
     * @return string
     */
    public function getReferencedEntity()
    {
        return $this->referencedEntity;
    }

    /**
     * Lazy load if possible
     *
     * @return bool
     */
    public function isLazy()
    {
        return $this->lazy;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = (string)$type;
        return $this;
    }

    /**
     * Set lazy flag
     *
     * @param bool $flag
     */
    public function setIsLazy($flag)
    {
        $this->lazy = (bool)$flag;
        return $this;
    }

    /**
     * Set the property to use for loading this entity
     *
     * @param string $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
        return $this;
    }
}