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

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature\MetadataFeature;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Where;

use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;
use Zend\Stdlib\Hydrator\ArraySerializable as ArrayObjectHydrator;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * Default table gateway class
 */
class TableGatewayRepository extends TableGateway implements RepositoryInterface, EntityManagerAwareInterface
{
    /**
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
    protected $hydrator = null;

    /**
     * @var string
     */
    protected $idField = 'id';

    /**
     * @var object
     */
    private $currentObject = null;

    /**
     * @var EntityManager
     */
    protected $entityManager = null;

    /**
     * @see \Zend\Db\TableGateway\TableGateway::__construct()
     */
    public function __construct($table, Adapter $adapter, $prototype, HydratorInterface $hydrator = null, $features = null)
    {
        if (!is_object($prototype)) {
            throw new exceptions\InvalidArgumentException(sprintf('$prototype must be an object, %s given', gettype($prototype)));
        }

        if ($features === null) {
            $features = array(
                new MetadataFeature(),
                new features\PopulateIdFeature(),
                new features\SanitizeDataFeature()
            );
        } else if ($features === false) {
            $features = null;
        }

        $this->hydrator = $hydrator? : $this->createHydrator($prototype);

        if ($prototype instanceof ResultSetInterface) {
            $resultSetPrototype = $prototype;
        } else {
            $resultSetPrototype = new EntityResultSet($this->hydrator, $prototype);
        }

        parent::__construct($table, $adapter, $features, $resultSetPrototype);
    }

    /**
     * @param object $prototype
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    protected function createHydrator($prototype)
    {
        if (($prototype instanceof HydratingResultSet) || ($prototype instanceof HydratorAwareInterface)) {
            return $prototype->getHydrator();
        } else if ($prototype instanceof ArraySerializableInterface) {
            return new ArrayObjectHydrator();
        }

        return new hydration\MappingHydrator(new ReflectionHydrator());
    }

    /**
     * {@inheritdoc}
     * @see \rampage\simpleorm\EntityManagerAwareInterface::setEntityManager()
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        return $this;
    }

    /**
     * @param HydratorInterface $hydrator
     * @return self
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;

        if (($this->resultSetPrototype instanceof HydratorAwareInterface) || ($this->resultSetPrototype instanceof HydratingResultSet)) {
            $this->resultSetPrototype->setHydrator($hydrator);
        }

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
     * @return \rampage\simpleorm\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
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
     * Register an object for persistence
     *
     * @param object $object
     * @return self
     */
    public function persist($object)
    {
        if (!$this->entityManager) {
            return $this->store($object);
        }

        $this->entityManager->persist($object);
        return $this;
    }

    /**
     * @see \rampage\simpleorm\RepositoryInterface::isObjectNew()
     */
    public function isObjectNew($object, array $data = null)
    {
        $idField = $this->getIdField();
        if (!is_string($idField)) {
            return false;
        }

        if ($data === null) {
            $data = $this->hydrator->extract($object);
        }

        return !isset($data[$idField]);
    }

    /**
     * Prepare data for update
     */
    protected function prepareUpdateData(array $data)
    {
        $idField = $this->getIdField();
        if (is_string($idField)) {
            unset($data[$idField]);
        }

        return $data;
    }

	/**
     * @param object $object
     */
    public function store($object)
    {
        if (!is_object($object)) {
            throw new exceptions\InvalidArgumentException(sprintf('%s expects an object, %s given.', __METHOD__, gettype($object)));
        }

        try {
            $this->currentObject = $object;
            $data = $this->hydrator->extract($object);

            if ($this->isObjectNew($object, $data)) {
                $this->insert($data);
                $this->currentObject = null;
                return $this;
            }

            $idField = $this->getIdField();
            $this->update($data, array($idField => $data[$idField]));
            $this->currentObject = null;
        } catch (\Exception $e) {
            $this->currentObject = null;
            throw $e;
        }

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

        return $result;
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

        $set = $this->prepareUpdateData($set);
        $result = (empty($set))? false : parent::update($set, $where);
        $this->currentObject = null;

        return $result;
    }

    /**
     * @see \Zend\Db\TableGateway\AbstractTableGateway::delete()
     */
    public function delete($object)
    {
        if (!is_object($object) || ($object instanceof Where) || ($object instanceof \Closure)) {
            return parent::delete($object);
        }

        $this->currentObject = $object;
        $data = $this->hydrator->extract($object);
        $idField = $this->getIdField();

        if (!is_string($idField) && !isset($data[$idField])) {
            return 0;
        }

        $where = array($idField => $data[$idField]);
        $result = parent::delete($where);
        $this->currentObject = null;

        return $result;
    }
}
