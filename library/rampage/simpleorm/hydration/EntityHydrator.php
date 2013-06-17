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

use Zend\Stdlib\Hydrator\AbstractHydrator;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Entity hydrator
 */
class EntityHydrator extends AbstractHydrator
{
    const TYPE_REFLECTION = 'reflection';
    const TYPE_ARRAY = 'array';
    const TYPE_REFERENCE = 'reference';

    /**
     * @var array
     */
    private $hydrators = array();

    /**
     * @var ReadOnlyFilter
     */
    private $readOnlyFilter = null;

    /**
     * @see \Zend\Stdlib\Hydrator\AbstractHydrator::__construct()
     */
    public function __construct(array $hydrators = null)
    {
        parent::__construct();

        if ($hydrators === null) {
            $this->addHydrator(self::TYPE_ARRAY, new ArraySerializableHydrator())
                ->addHydrator(self::TYPE_REFLECTION, new ReflectionHydrator());
                //->addHydrator(self::TYPE_REFERENCE, new ReferenceHydrator());
        } else {
            foreach ($hydrators as $name => $hydrator) {
                $this->addHydrator($name, $hydrator);
            }
        }

        $this->readOnlyFilter = new ReadOnlyFilter();
    }

    /**
     * Read only filter
     */
    public function getReadOnlyFilter()
    {
        return $this->readOnlyFilter;
    }

    /**
     * @param string $name
     * @param HydratorInterface $hydrator
     * @return self
     */
    public function addHydrator($name, HydratorInterface $hydrator)
    {
        $this->hydrators[$name] = $hydrator;
        return $this;
    }

    /**
     * @param string $name
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getHydrator($name)
    {
        if (!isset($this->hydrators[$name])) {
            return null;
        }

        return $this->hydrators[$name];
    }

    /**
     * @return \Zend\Stdlib\Hydrator\Reflection
     */
    public function getReflectionHydrator()
    {
        return $this->reflectionHydrator;
    }

    /**
     * @return \rampage\simpleorm\hydration\ArraySerializableHydrator
     */
    public function getArrayHydrator()
    {
        return $this->arrayHydrator;
    }

    /**
     * @param object $object
     * @param array $data
     */
    protected function extractByHydrator(HydratorInterface $hydrator, $object, array &$data)
    {
        if ($hydrator instanceof AbstractHydrator) {
            $hydrator->strategies = $this->strategies;
            $hydrator->addFilter('readonly', $this->getReadOnlyFilter());
        }

        $extracted = $hydrator->extract($object);
        $data = array_merge($data, $extracted);

        if ($hydrator instanceof AbstractHydrator) {
            $hydrator->removeFilter('readonly');
        }

        return $this;
    }

    /**
     * @param HydratorInterface $hydrator
     * @param array $data
     * @param object $object
     */
    protected function hydrateByHydrator(HydratorInterface $hydrator, array $data, $object)
    {
        if ($hydrator instanceof AbstractHydrator) {
            $hydrator->strategies = $this->strategies;
        }

        $hydrator->hydrate($data, $object);
        return $this;
    }

    /**
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::extract()
     */
    public function extract($object)
    {
        $data = array();

        foreach ($this->hydrators as $hydrator) {
            $this->extractByHydrator($hydrator, $object, $data);
        }

        return $data;
    }

    /**
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        foreach ($this->hydrators as $hydrator) {
            $this->hydrateByHydrator($hydrator, $data, $object);
        }

        return $object;
    }
}
