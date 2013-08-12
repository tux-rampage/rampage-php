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

namespace rampage\simpleorm;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Exception\InvalidArgumentException;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Default table gateway class
 */
class DefaultRepository extends TableGateway
{
    /**
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
    private $hydrator = null;

    /**
     * @var string
     */
    protected $idField = 'id';

    /**
     * @var object
     */
    private $currentObject = null;

    /**
     * @see \Zend\Db\TableGateway\TableGateway::__construct()
     */
    public function __construct($table, AdapterInterface $adapter, $features = null, ResultSetInterface $resultSetPrototype = null, Sql $sql = null)
    {
        $this->hydrator = new ReflectionMappingHydrator();
        parent::__construct($table, $adapter, $features, $resultSetPrototype, $sql);
    }

    /**
     * @param HydratorInterface $hydrator
     * @return \luka\wp\integration\DefaultRepository
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * @return object
     */
    public function getCurrentObject()
    {
        return $this->currentObject;
    }

    /**
     * @return string
     */
    public function getIdField()
    {
        return $this->idField;
    }

    /**
     * @param string $field
     * @return self
     */
    public function setIdField($field)
    {
        $this->idField = $field;
        return $this;
    }

    /**
     * @param int|string $id
     * @return object|array
     */
    public function fetchOneById($id)
    {
        return $this->select(array($this->getIdField() => $id));
    }

    /**
     * @return object|array
     */
    public function fetchAll()
    {
        return $this->select();
    }

    /**
     * @param object $object
     */
    public function persist($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException(sprintf('%s expects an object, %s given.', __METHOD__, gettype($object)));
        }

        $this->currentObject = $object;

        $idField = $this->getIdField();
        $data = $this->hydrator->extract($object);

        if (!is_string($idField) || !isset($data[$idField])) {
            $this->insert($data);
            $this->currentObject = null;
            return $this;
        }

        $id = $data[$idField];
        unset($data[$idField]);

        $this->update($data, array($idField => $id));
        $this->currentObject = null;

        return $this;
    }

    /**
     * @see \Zend\Db\TableGateway\AbstractTableGateway::insert()
     */
    public function insert($set)
    {
        if (!is_object($set)) {
            return parent::insert($set);
        }

        $this->currentObject = $set;
        $set = $this->hydrator->extract($set);

        $result = parent::insert($set);
        $this->currentObject = null;

        return $this;
    }

	/**
     * @see \Zend\Db\TableGateway\AbstractTableGateway::update()
     */
    public function update($set, $where = null)
    {
        if (!is_object($set)) {
            return parent::update($set, $where);
        }

        $this->currentObject = $set;
        $set = $this->hydrator->extract($set);
        $idField = $this->getIdField();

        if (($where === null) && is_string($idField) && isset($set[$idField])) {
            $where = array($idField => $set[$idField]);
        }

        $result = parent::update($set, $where);
        $this->currentObject = null;

        return $this;
    }
}
