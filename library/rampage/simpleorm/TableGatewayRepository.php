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
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Where;

use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;
use Zend\Stdlib\Hydrator\ArraySerializable as ArrayObjectHydrator;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventInterface;

/**
 * Default table gateway class
 */
class TableGatewayRepository extends TableGateway implements RepositoryInterface
{
    /**
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
    private $hydrator = null;

    /**
     * @var EntityManager
     */
    private $entityManager = null;

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
    public function __construct($table, EntityManager $entityManager, $prototype, $features = null)
    {
        if (!is_object($prototype)) {
            throw new exceptions\InvalidArgumentException(sprintf('$prototype must be an object, %s given', gettype($prototype)));
        }

        $this->entityManager = $entityManager;
        if (!$this->hydrator) {
            $this->hydrator = ($prototype instanceof ArraySerializableInterface)? new ArrayObjectHydrator() : new hydration\MappingHydrator(new ReflectionHydrator());
        }

        if ($prototype instanceof ResultSetInterface) {
            $resultSetPrototype = $prototype;
        } else {
            $resultSetPrototype = new EntityResultSet($this->hydrator, $prototype);
        }

        if ($resultSetPrototype instanceof EventManagerAwareInterface) {
            $resultSetPrototype->getEventManager()->attach('hydrate', array($this, 'onResultHydration'));
        }

        parent::__construct($table, $entityManager->getAdapter(), $features, $resultSetPrototype);
    }

    public function onResultHydration(EventInterface $event)
    {
        $object = $event->getTarget();
        $data = $event->getParam('data');

        if (!is_object($object) || !is_array($data)) {
            return;
        }

        $state = $this->getUnitOfWork()->getObjectState($object);
        if (!$state) {
            $state = new ObjectPersistenceState($data);
            $this->getUnitOfWork()->setObjectState($object, $state);
        } else {
            $state->setData($data);
        }
    }

    /**
     * @param HydratorInterface $hydrator
     * @return self
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
     * @return \rampage\simpleorm\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return \rampage\simpleorm\UnitOfWorkInterface
     */
    public function getUnitOfWork()
    {
        return $this->entityManager->getUnitOfWork();
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

        $this->currentObject = $object;

        $idField = $this->getIdField();
        $data = $this->hydrator->extract($object);

        if ($this->isObjectNew($object, $data)) {
            $this->insert($data);
            $this->currentObject = null;
            return $this;
        }

        $idField = $this->getIdField();
        $this->update($data, array($idField => $data[$idField]));
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
