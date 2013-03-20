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

use rampage\core\data\ArrayExchangeInterface;

use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Zend\Stdlib\Hydrator\StrategyEnabledInterface;
use Zend\Stdlib\Hydrator\ArraySerializable as ArraySerializableHydrator;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;

/**
 * Entity
 */
class ProxyHydrator extends AbstractHydrator
{
    /**
     * Internal hydration strategy
     *
     * @var StrategyEnabledInterface|HydratorInterface|string
     */
    protected $strategy = null;

    /**
     * Injected strategies
     *
     * @var array
     */
    private $injectedStrategies = array();

    /**
     * Inner hydrator
     *
     * @var HydratorInterface
     */
    private $innerHydrator = null;

    /**
     * Hydrator strategy
     *
     * @param HydratorInterface|string|null $strategy
     * @return \rampage\orm\db\hydrator\EntityHydrator
     */
    public function setHydratorStrategy($strategy)
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * Inject strategies to inner hydrator
     *
     * @return \rampage\orm\db\hydrator\ProxyHydrator
     */
    private function prepareInnerHydrator($object)
    {
        $this->injectedStrategies = array();
        $this->innerHydrator = $this->initInnerHydrator($object);

        if (!$this->strategy instanceof StrategyEnabledInterface) {
            return $this;
        }

        foreach ($this->strategies as $name => $strategy) {
            if ($this->strategy->hasStrategy($name)) {
                continue;
            }

            $this->injectedStrategies[$name] = $name;
            $this->strategy->addStrategy($name, $strategy);
        }

        return $this;
    }

    /**
     * Remove injected strategies from inner hydrator
     *
     * @return \rampage\orm\db\hydrator\ProxyHydrator
     */
    private function destroyInnerHydrator()
    {
        $this->innerHydrator = null;

        if (!$this->strategy instanceof StrategyEnabledInterface) {
            $this->injectedStrategies = array();
            return $this;
        }

        foreach ($this->injectedStrategies as $name) {
            $this->strategy->removeStrategy($name);
        }

        $this->injectedStrategies = array();
        return $this;
    }

    /**
     * Returns the current inner hydrator
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    protected function getInnerHydrator()
    {
        return $this->innerHydrator;
    }

    /**
     * Returns the inner hydrator for the given object
     *
     * @param object $object
     * @return HydratorInterface|string|null
     */
    protected function initInnerHydrator($object)
    {
        if ($this->strategy instanceof HydratorInterface) {
            return $this->strategy;
        }

        $hydrator = null;

        switch ($this->strategy) {
            case 'array':
                $hydrator = new ArraySerializableHydrator();
                break;

            case 'reflection':
                $hydrator = new ReflectionHydrator();
                break;

            case 'methods':
                $hydrator = new ClassMethodsHydrator();
                break;

            default:
                if ($object instanceof ArrayExchangeInterface) {
                    $hydrator = new ArraySerializableHydrator();
                } else {
                    $hydrator = new ClassMethodsHydrator();
                }

                break;
        }

        $hydrator->strategies = $this->strategies;
        $hydrator->filterComposite = $this->filterComposite;
        return $hydrator;
    }

    /**
     * Internal extract
     *
     * Overwrite this if you need the internal hydrator with
     * injected dependencies
     *
     * @param object $object
     * @return array
     */
    protected function internalExtract($object)
    {
        return $this->getInnerHydrator()->extract($object);
    }

    /**
     * Internal hydrate
     *
     * Overwrite this if you need the internal hydrator
     * with injected strategies
     *
     * @param array $data
     * @param object $object
     */
    protected function internalHydrate(array $data, $object)
    {
        $this->getInnerHydrator()->hydrate($data, $object);
    }

    /**
     * Get inner hydrator strategy
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface|string
     */
    protected function getHydratorStrategy()
    {
        if ($this->innerHydrator instanceof ProxyHydrator) {
            return $this->innerHydrator->getHydratorStrategy();
        }

        return $this->strategy;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::extract()
     */
    public function extract($object)
    {
        $this->prepareInnerHydrator($object);
        $data = $this->internalExtract($object);
        $this->destroyInnerHydrator($object);

        return $data;
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        $this->prepareInnerHydrator($object);
        $this->internalHydrate($data, $object);
        $this->destroyInnerHydrator();
    }
}