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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\db\platform\hydrator;

use rampage\orm\db\platform\FieldMapper;
use Zend\Stdlib\Hydrator\ArraySerializable;
use Zend\Stdlib\Exception;

/**
 * Default hydration
 */
class FieldHydrator extends ArraySerializable implements FieldHydratorInterface, MappingHydratorInterface
{
    /**
     * Field mapper instance
     *
     * @var FieldMapper
     */
    private $fieldMapper = null;

    /**
     * Allowed fields
     *
     * @var array
     */
    private $allowedFields = null;

    /**
     * Allowed fields
     *
     * @return string
     */
    public function getAllowedFields()
    {
        return $this->allowedFields;
    }

	/**
     * Set allowed fields
     *
     * @param array $allowedFields
     */
    public function setAllowedFields(array $allowedFields = null)
    {
        $this->allowedFields = $allowedFields;
        return $this;
    }

	/**
     * Returns the field mapper instance
     *
     * @return \rampage\orm\db\platform\FieldMapper
     */
    public function getFieldMapper()
    {
        return $this->fieldMapper;
    }

    /**
     * Set the fieldmapper instance
     *
     * @param \rampage\orm\db\platform\FieldMapper $fieldMapper
     */
    public function setFieldMapper(FieldMapper $fieldMapper)
    {
        $this->fieldMapper = $fieldMapper;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Stdlib\Hydrator\ArraySerializable::extract()
     */
    public function extract($object)
    {
        if (!is_callable(array($object, 'getArrayCopy'))) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s expects the provided object to implement getArrayCopy()', __METHOD__
            ));
        }

        $self = $this;
        $data = array();
        $mapper = $this->getFieldMapper();
        $allowed = $this->getAllowedFields();

        foreach ($object->getArrayCopy() as $name => $value) {
            $key = ($mapper)? $mapper->mapAttribute($name) : $name;
            if (!$this->getFilter()->filter($name) || ($allowed && !in_array($key, $allowed))) {
                continue;
            }

            $data[$key] = $this->extractValue($name, $value);
        }

        return $data;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Stdlib\Hydrator\ArraySerializable::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        $mapped = array();
        $mapper = $this->getFieldMapper();

        foreach ($data as $key => $value) {
            $name = ($mapper)? $mapper->mapField($key) : $key;
            $mapped[$name] = $this->hydrateValue($name, $value);
        }

        if (method_exists($object, 'exchangeArray') && is_callable(array($object, 'exchangeArray'))) {
            $object->exchangeArray($mapped);
        } elseif (is_callable(array($object, 'populate'))) {
            $object->populate($mapped);
        } else {
            throw new Exception\BadMethodCallException(sprintf(
                '%s expects the provided object to implement exchangeArray() or populate()', __METHOD__
            ));
        }

        return $object;
    }
}