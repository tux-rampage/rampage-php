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

namespace rampage\simpleorm\mongodb;

use rampage\simpleorm\RepositoryInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

use MongoDB;
use MongoId;

/**
 * Mongo repo
 */
class MongoDbRepository implements RepositoryInterface
{
    /**
     * @var HydratorInterface
     */
    private $hydrator = null;

    /**
     * @var \MongoDB
     */
    private $connection = null;

    /**
     * @var \MongoCollection
     */
    private $collection = null;

    /**
     * @var ResultSetInterface
     */
    protected $resultSetPrototype = null;

    /**
     * @param MongoDB $connection
     * @param HydratorInterface $hydrator
     */
    public function __construct($collection, MongoDB $connection, HydratorInterface $hydrator, ResultSetInterface $resultSetPrototype)
    {
        $this->connection = $connection;
        $this->collection = $connection->selectCollection($collection);
        $this->hydrator = $hydrator;
        $this->resultSetPrototype = $resultSetPrototype;
    }

    /**
     * @param object $object
     * @return MongoId
     */
    protected function getObjectId($object)
    {
        $id = $object->getId();
        return $this->ensureMongoId($id);
    }

    /**
     * @param string $id
     * @return \MongoId|null
     */
    protected function ensureMongoId($id)
    {
        if ($id && !($id instanceof MongoId)) {
            $id = new MongoId($id);
        }

        return $id;
    }

    /**
     * @see \rampage\simpleorm\RepositoryInterface::getHydrator()
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * @see \rampage\simpleorm\RepositoryInterface::isObjectNew()
     */
    public function isObjectNew($object)
    {
        return ($this->getObjectId($object))? false : true;
    }

	/**
     * @see \rampage\simpleorm\PersistenceGatewayInterface::delete()
     */
    public function delete($object)
    {
        $id = $this->getObjectId($object);
        if (!$id) {
            return $this;
        }

        $this->collection->remove(array('_id' => $id));
        return $this;
    }

    /**
     * @see \rampage\simpleorm\PersistenceGatewayInterface::store()
     */
    public function store($object)
    {
        $data = $this->hydrator->extract($object);

        if ($this->isObjectNew($object)) {
            $id = $this->collection->insert($data);
            $this->setObjectId($object, $id);
            return $this;
        }

        unset($data['_id']);
        $this->collection->update(array('_id' => $this->getObjectId($object)), $data);

        return $this;
    }

    /**
     * @param array $query
     * @param array $fields
     * @return ResultSetInterface
     */
    public function find(array $query = null, array $fields = null)
    {
        $cursor = $this->collection->find($query, $fields);
        $result = clone $this->resultSetPrototype;

        $result->initialize($cursor);

        return $result;
    }

    /**
     * @param array $query
     * @param array $fields
     * @return ResultSetInterface
     */
    public function findOne(array $query = null, array $fields = null)
    {
        $data = $this->collection->findOne($query, $fields);
        if (!$data) {
            return null;
        }

        $result = clone $this->resultSetPrototype;
        $result->initialize(new ArrayCursor(array($data)));

        return $result->current();
    }

    /**
     * @return object
     */
    public function fetchOneById($id)
    {
        return $this->execFindOne(array('_id' => $id));
    }
}
