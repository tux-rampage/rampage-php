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

use rampage\simpleorm\exception;
use rampage\core\data\ArrayExchangeInterface;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use ArrayObject;

class ArraySerializableHydrator extends AbstractHydrator
{
    /**
     * @see \Zend\Stdlib\Hydrator\ArraySerializable::extract()
     */
    public function extract($object)
    {
        if (!($object instanceof ArrayExchangeInterface) && !($object instanceof ArrayObject) ) {
            return array();
        }

        $data = array();

        foreach ($object->getArrayCopy() as $key => $value) {
            if (!$this->getFilter()->filter($key)) {
                continue;
            }

            $data[$key] = $this->extractValue($key, $value);
        }

        return $data;
    }

    /**
     * @see \Zend\Stdlib\Hydrator\ArraySerializable::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        if (!($object instanceof ArrayExchangeInterface) && !($object instanceof ArrayObject)) {
            return $object;
        }

        $self = $this;

        array_walk($data, function (&$value, $name) use ($self) {
            $value = $self->hydrateValue($name, $value);
        });

        if ($object instanceof ArrayExchangeInterface) {
            $object->populate($data);
        } else {
            $object->exchangeArray($data);
        }

        return $object;
    }
}
