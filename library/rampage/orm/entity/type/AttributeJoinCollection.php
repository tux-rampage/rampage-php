<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2012 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 * @version   $Id$
 */

namespace rampage\orm\entity\type;

use IteratorAggregate;
use ArrayObject;

/**
 * Collection for joined attributes
 */
class AttributeJoinCollection implements IteratorAggregate
{
    /**
     * Entity type name
     *
     * @var string
     */
    protected $entityTypeName = null;

    /**
     * Attributes
     *
     * @var ArrayObject
     */
    private $attributes = null;

    /**
     * Entity type instance of this collection
     *
     * @var EntityType
     */
    private $parent = null;

    /**
     * Construct
     *
     * @param EntityType $parent
     * @param string $entityTypeName
     */
    public function __construct(EntityType $parent, $entityTypeName)
    {
        $this->attributes = new ArrayObject();
        $this->parent = $parent;
        $this->entityTypeName = $entityTypeName;
    }

    /**
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return $this->attributes;
    }

    /**
     * Returns the entity type name
     *
     * @return string
     */
    public function getEntityTypeName()
    {
        return $this->entityTypeName;
    }

    /**
     * Add a join attribute
     *
     * @param unknown $attribute
     * @param unknown $reference
     * @param unknown $type
     */
    public function addAttribute($attribute, $reference = null, $type = null)
    {
        if (!$attribute instanceof AttributeJoinReference) {
            $attribute = new AttributeJoinReference($attribute, $reference, $type);
        }

        $this->attributes[$attribute->getName()] = $attribute;
        if (!$this->parent->hasAttribute($attribute->getName())) {
            $this->parent->addAttribute($attribute);
        }

        return $this;
    }
}