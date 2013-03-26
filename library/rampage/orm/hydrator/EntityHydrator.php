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

use rampage\orm\entity\type\EntityType;
use rampage\orm\entity\type\Attribute;
use rampage\orm\entity\type\Reference;

/**
 * Entity
 */
class EntityHydrator extends ProxyHydrator
{
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
     * Hydration manager
     *
     * @var HydrationManager
     */
    private $referenceHydrationManager = null;

    /**
     * Construct
     */
    public function __construct(EntityType $entityType, HydrationManager $referenceHydrationManager)
    {
        $this->entityType = $entityType;
        $this->referenceHydrationManager = $referenceHydrationManager;

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
     * Reference hydration manager
     *
     * @return \rampage\orm\hydrator\HydrationManager
     */
    protected function getReferenceHydrationManager()
    {
        return $this->referenceHydrationManager;
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
     * Hydrate an entity reference and return its value
     *
     * @param Reference $reference
     * @param array $data
     * @param object $object
     */
    private function hydrateReference(Reference $reference, array $data, $object, &$value)
    {
        $property = $reference->getProperty();
        $hydrationManager = $this->getReferenceHydrationManager();

        if (!$property || !$this->hasStrategy($property) || !$hydrationManager->has($reference->getHydration())) {
            return false;
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
            return false;
        }

        $value = $this->getStrategy($property)->hydrate($value);
        return true;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\hydrator\ProxyHydrator::internalHydrate()
     */
    protected function internalHydrate(array $data, $object)
    {
        parent::internalHydrate($data, $object);

        $references = array();
        foreach ($this->getEntityType()->getReferences() as $reference) {
            $value = null;

            if (!$this->hydrateReference($reference, $data, $object, $value)) {
                continue;
            }

            $hydration = strtolower($reference->getHydration());
            $property = $reference->getProperty();
            $references[$hydration][$property] = $value;
        }

        foreach ($references as $hydration => $referenceData) {
            $this->getReferenceHydrationManager()
                ->get($hydration)
                ->hydrate($referenceData, $object);
        }

        return $this;
    }
}