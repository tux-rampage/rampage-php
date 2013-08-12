<?php
/**
 * This file is part of luka-wp-integration.
 *
 * luka-wp-integration is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * luka-wp-integration is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with mage-secure-cookie.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2013 LUKA netconsult GmbH (www.luka.de)
 * @license   http://www.gnu.org/licenses/lgpl-3.0.txt
 */

namespace rampage\simpleorm;

use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;

/**
 * Hydrator implementation that maps field names from camelCase to underscore and vice versa
 */
class ReflectionMappingHydrator extends ReflectionHydrator
{
    /**
     * @param string $name
     * @return string
     */
    protected function toUnderscore($name)
    {
        $name = preg_replace_callback('~(\B)([A-Z])~', '$1_$2', $name);
        $name = strtolower($name);

        return $name;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function toCamelCase($name)
    {
        $name = strtr($name, '_', ' ');
        $name = ucwords($name);
        $name = lcfirst(strtr($name, ' ', ''));

        return $name;
    }

    /**
     * @see \Zend\Stdlib\Hydrator\Reflection::extract()
     */
    public function extract($object)
    {
        $raw = parent::extract($object);
        $data = array();

        foreach ($raw as $key => $value) {
            $key = $this->toUnderscore($key);
            $data[$key] = $value;
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
            $key = $this->toCamelCase($key);
            $hydratableData[$key] = $value;
        }

        return parent::hydrate($hydratableData, $object);
    }
}
