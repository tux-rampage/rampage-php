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

namespace rampage\orm\hydrator;

use rampage\core\Utils;
use rampage\orm\entity\type\EntityType;
use rampage\orm\entity\type\Attribute;
use rampage\orm\entity\type\Reference;
use rampage\orm\entity\lazy\EntityInterface as LazyEntityInterface;

/**
 * Entity
 */
class EntityHydrator extends ProxyHydrator
{
    /**
     * Reflection properties by class
     *
     * @var array
     */
    private static $reflectionProperties = array();

    /**
     * entity type
     *
     * @var EntityType
     */
    private $entityType = null;

    /**
     * Add a strategy
     *
     * @var unknown
     */
    protected $strategy = null;

    /**
     * Construct
     */
    public function __construct(EntityType $entityType)
    {
        $this->entityType = $entityType;

        parent::__construct();

        /* @var $attribute \rampage\orm\entity\type\Attribute */
        foreach ($entityType->getAttributes() as $attribute) {
            $strategy = $this->getAttributeStrategy($attribute);
            if (!$strategy) {
                continue;
            }

            $this->addStrategy($attribute->getName(), $strategy);
        }
    }

    /**
     * Returns the reflection property
     *
     * @param object|string $object
     * @param string $name
     * @return \ReflectionProperty
     */
    protected static function getReflectionProperty($object, $name)
    {
        $class = (is_object($object))? get_class($object) : (string)$object;

        if (isset(static::$reflectionProperties[$class][$name])) {
            return static::$reflectionProperties[$class][$name];
        }

        $reflection = new \ReflectionClass($class);
        $property = $reflection->getProperty($name);

        $property->setAccessible(true);
        static::$reflectionProperties[$class][$name] = $property;

        return $property;
    }

    /**
     * @return \rampage\orm\entity\type\EntityType
     */
    protected function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Attribute strategy
     *
     * @param Attribute $attribute
     * @return null|StrategyInterface
     */
    protected function getAttributeStrategy(Attribute $attribute)
    {
        $type = strtolower($attribute->getType());

        switch (strtolower($attribute->getType())) {
            case 'date':
                return new strategy\DateStrategy();

            case 'datetime':
                return new strategy\DateTimeStrategy();

            case 'string':
            case 'int':
            case 'integer':
            case 'float':
            case 'bool':
            case 'boolean':
                return new strategy\ScalarStrategy($type);
        }

        return null;
    }

    /**
     * Hydrate an entity reference
     *
     * @param Reference $reference
     * @param array $data
     * @param object $object
     */
    protected function hydrateReference(Reference $reference, array $data, $object)
    {
        $property = $reference->getProperty();
        if (!$property || !$this->hasStrategy($property)) {
            return $this;
        }

        $value = array();

        /* @var $attributeReference \rampage\orm\entity\type\AttributeReference */
        foreach ($reference->getAttributeReferences() as $attributeReference) {
            $refName = $attributeReference->getReferencedAttribute();
            if ($attributeReference->isLiteral()) {
                $value[$refName] = $attributeReference->getLiteral();
                continue;
            }

            $attribute = $attributeReference->getAttribute();
            $value[$refName] = (isset($data[$attribute]))? $data[$attribute] : null;
        }

        if (empty($value)) {
            return $this;
        }

        $value = $this->getStrategy($property)->hydrate($value);

        if ($reference->isLazy() && ($object instanceof LazyEntityInterface) && ($value instanceof LazyValueInterface)) {
            $object->addLazyAttribute($property, $value);
        }

        if ($value instanceof LazyValueInterface) {
            $value = $value($property);
        }

        $property = Utils::camelize($property);

        if ($this->getHydratorStrategy() == 'reflection') {
            $reflection = $this->getReflectionProperty($object, lcfirst($property));
            $reflection->setValue($object, $value);
            return $this;
        }

        $method = 'set' . $property;
        if (is_callable(array($object, $method))) {
            $object->$method($property);
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\hydrator\ProxyHydrator::internalHydrate()
     */
    protected function internalHydrate(array $data, $object)
    {
        parent::internalHydrate($data, $object);

        foreach ($this->getEntityType()->getReferences() as $reference) {
            $this->hydrateReference($reference, $data, $object);
        }

        return $this;
    }
}