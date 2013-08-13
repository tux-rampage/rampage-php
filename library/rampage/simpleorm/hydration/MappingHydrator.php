<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2013 Axel Helmert
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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm\hydration;

use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use rampage\core\Utils;


/**
 * Hydrator implementation that maps field names from camelCase to underscore and vice versa
 */
class MappingHydrator extends AbstractHydrator
{
    /**
     * @var HydratorInterface
     */
    protected $innerHydrator = null;

    /**
     * @var array
     */
    protected $map = array();

    /**
     * @param HydratorInterface $innerHydrator
     * @param array $map
     */
    public function __construct(HydratorInterface $innerHydrator, array $map = array())
    {
        $this->innerHydrator = $innerHydrator;
        $this->map = $map;
    }

    /**
     * @param string $name
     * @return string|boolean
     */
    protected function mapPropertyName($name)
    {
        if (isset($this->map[$name])) {
            return $this->map[$name];
        }

        return $this->toUnderscore($name);
    }

    /**
     * @param string $name
     * @return string|false
     */
    protected function mapKeyName($name)
    {
        $property = array_search($name, $this->map);
        if (!$property) {
            $property = $this->toCamelCase($name);
        }

        return $property;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function toUnderscore($name)
    {
        return Utils::underscore($name);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function toCamelCase($name)
    {
        return lcfirst(Utils::camelize($name));
    }

    /**
     * @see \Zend\Stdlib\Hydrator\Reflection::extract()
     */
    public function extract($object)
    {
        $raw = $this->innerHydrator->extract($object);
        $filter = $this->getFilter();
        $data = array();

        foreach ($raw as $key => $value) {
            $key = $this->mapPropertyName($key);

            if ($filter->filter($key)) {
                continue;
            }

            $data[$key] = $this->extractValue($key, $value, $object);
        }

        return $data;
    }

	/**
     * @see \Zend\Stdlib\Hydrator\Reflection::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        $hydratableData = array();

        foreach ($data as $key => $value) {
            $value = $this->hydrateValue($key, $value, $data);
            $key = $this->mapKeyName($key);
            $hydratableData[$key] = $value;
        }

        return $this->innerHydrator->hydrate($hydratableData, $object);
    }
}
