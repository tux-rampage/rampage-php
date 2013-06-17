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

namespace rampage\simpleorm\hydration;

use rampage\simpleorm\metadata\Entity;
use rampage\simpleorm\metadata\Attribute;

use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

use SplObjectStorage;

/**
 * Hydrator factory
 */
class HydratorFactory
{
    /**
     * @var TypeStrategyFactory
     */
    private $typeStrategyFactory = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->typeStrategyFactory = new TypeStrategyFactory();
    }

    /**
     * @param TypeStrategyFactory $factory
     * @return \rampage\simpleorm\hydration\HydratorFactory
     */
    protected function setTypeStrategyFactory(TypeStrategyFactory $factory)
    {
        $this->typeStrategyFactory = $factory;
        return $this;
    }

    /**
     * @param AbstractHydrator $hydrator
     * @return \rampage\simpleorm\hydration\PropertyFilter
     */
    protected function getPropertyFilter(SplObjectStorage $filters, AbstractHydrator $hydrator)
    {
        if (!$filters->contains($hydrator)) {
            $filter = new PropertyFilter();

            $hydrator->addFilter('allowed_properties', $filter);
            $filters->attach($hydrator, $filter);
        }

        return $filters[$hydrator];
    }

    /**
     * @param Attribute $attribute
     * @return null|\Zend\Stdlib\Hydrator\Strategy\StrategyInterface
     */
    protected function getStrategy(Attribute $attribute)
    {
        if ($strategy = $attribute->getHydrationStrategy()) {
            if ($strategy instanceof StrategyInterface) {
                return $strategy;
            }
        } else {
            $strategy = $attribute->getType();
        }

        return $this->strategyFactory->createStrategy($attribute->getType());
    }

    /**
     * @param Attribute $attribute
     */
    protected function initAttributeHydration(HydratorInterface $hydrator, Attribute $attribute)
    {
        if (!$hydrator instanceof EntityHydrator) {
            return $this;
        }

        if ($strategy = $this->getStrategy($attribute)) {
            $hydrator->addStrategy($attribute->getName(), $strategy);
        }

        $typeHydrator = $hydrator->getReflectionHydrator($attribute->getHydrationType());

        if ($typeHydrator instanceof AbstractHydrator) {
            $this->getPropertyFilter($typeHydrator)->addField($attribute->getName());
        }

        if ($attribute->isReadOnly()) {
            $hydrator->getReadOnlyFilter()->addProperty($attribute->getName());
        }

        return $this;
    }

    /**
     * Create hydrator instance for the given entity
     *
     * @param Entity $entity
     */
    public function createHydrator(Entity $entity)
    {
        $this->fieldFilters->removeAll($this->fieldFilters);
        $instance = new EntityHydrator();

        foreach ($entity->getAttributes() as $attribute) {
            $this->initAttributeHydration($instance, $attribute);
        }

        return $instance;
    }
}
