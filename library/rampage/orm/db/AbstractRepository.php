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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\db;

use rampage\core\ObjectManagerInterface;
use rampage\orm\RepositoryInterface;
use rampage\orm\repository\PersistenceFeatureInterface;
use rampage\orm\ConfigInterface;
use rampage\orm\query\QueryInterface;
use rampage\orm\db\adapter\AdapterAggregate;

use rampage\orm\entity\CollectionInterface;
use rampage\orm\entity\EntityInterface;
use rampage\orm\entity\lazy\Collection as LazyCollection;
use rampage\orm\entity\lazy\CollectionInterface as  LazyCollectionInterface;
use rampage\orm\entity\feature\QueryableCollectionInterface;

use SplObjectStorage;
use rampage\orm\db\lazy\CollectionLoadDelegate;

/**
 * Abstract DB repository
 */
class AbstractRepository implements RepositoryInterface, PersistenceFeatureInterface
{
    /**
     * The query mapper for this repository
     *
     * @var string
     */
    private $queryMapper = null;

    /**
     * Repository name
     *
     * @var string
     */
    private $name = null;

    /**
     * Database adapter name
     *
     * @var string
     */
    protected $adapterName = null;

    /**
     * Object manager
     *
     * @var \rampage\core\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Read adapter
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $read = null;

    /**
     * Write adapter
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $write = null;

    /**
     * Repository Config
     *
     * @var \rampage\orm\ConfigInterface
     */
    private $config = null;

    /**
     * Construct
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->platforms = new SplObjectStorage();
    }

    /**
     * Object manager
     *
     * @return \rampage\core\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Create a new adapter aggregate
     *
     * @param string $name
     * @return \rampage\orm\db\adapter\AdapterAggregate
     */
    protected function newAdapterAggregate($name)
    {
        return $this->getObjectManager()->get('rampage.orm.db.adapter.AdapterAggregate', array(
            'adapterName' => $name
        ));
    }

	/**
     * Read adapter aggregate
     *
     * @return \rampage\orm\db\adapter\AdapterAggregate
     */
    protected function getReadAggregate()
    {
        if ($this->read) {
            return $this->read;
        }

        $read = $this->newAdapterAggregate($this->adapterName . '.read');
        $this->setReadAggregate($read);

        return $read;
    }

	/**
     * Returns the read adapter
     *
     * @param \rampage\orm\db\adapter\AdapterAggregate $read
     */
    public function setReadAggregate(AdapterAggregate $read)
    {
        $this->read = $read;
        return $this;
    }

	/**
     * Returns the write adapter
     *
     * @return \rampage\orm\db\adapter\AdapterAggregate
     */
    protected function getWriteAggregate()
    {
        if ($this->write) {
            return $this->write;
        }

        $write = $this->newAdapterAggregate($this->adapterName . '.write');
        $this->setWriteAggregate($write);

        return $write;
    }

    /**
     * Set the write adapter
     *
     * @param \rampage\orm\db\adapter\AdapterAggregate $write
     */
    protected function setWriteAggregate(AdapterAggregate $write)
    {
        $this->write = $write;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\RepositoryInterface::getName()
     */
    public function getName()
    {
        return $this->name;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\RepositoryInterface::setName()
     */
    public function setName($name)
    {
        return $this->name;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\RepositoryInterface::setAdapterName()
     */
    public function setAdapterName($name)
    {
        $this->adapterName = $name;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\RepositoryInterface::setConfig()
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get query mapper
     *
     * @return \rampage\orm\db\query\MapperInterface
     */
    protected function getQueryMapper(QueryInterface $query)
    {
        return $this->getObjectManager()->get('rampage.orm.db.query.DefaultMapper');
    }

    /**
     * Create a new collection
     *
     * @param QueryInterface $query
     * @return \rampage\orm\entity\LazyLoadableCollection
     */
    protected function newCollection(QueryInterface $query)
    {
        return new LazyCollection();
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::delete()
     */
    public function delete(EntityInterface $entity)
    {

    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::getCollection()
     */
    public function getCollection(QueryInterface $query)
    {
        $collection = $this->newCollection($query);

        if ($collection instanceof QueryableCollectionInterface) {
            $collection->setPersistenceQuery($query);
        }

        if ($collection instanceof LazyCollectionInterface) {
            $collection->setLoaderDelegate(new CollectionLoadDelegate($this, $query));
            return $collection;
        }

        $this->loadCollectionSize($collection, $query);
        $this->loadCollection($collection, $query);

        return $collection;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::load()
     */
    public function load($id, $entity)
    {
        // TODO Auto-generated method stub

    }

    /**
     * Load the collection size
     *
     * @param CollectionInterface $collection
     * @param QueryInterface $query
     * @return \rampage\orm\db\AbstractRepository
     */
    public function loadCollectionSize(CollectionInterface $collection, QueryInterface $query)
    {
        $mapper = $this->getQueryMapper($query);
        $sql = $this->getReadAggregate()->sql();
        $select = $this->getQueryMapper($query)->mapToSizeSelect($query, $sql->select());
        $result = $sql->prepareStatementForSqlObject($select)->execute()->current();

        $size = (is_array($result) && isset($result['size']))? (int)$result['size'] : 0;
        $collection->setSize($size);

        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::loadCollection()
     */
    public function loadCollection(CollectionInterface $collection, QueryInterface $query)
    {
        $mapper = $this->getQueryMapper($query);
        $sql = $this->getReadAggregate()->sql();
        $select = $sql->select();

        $mapper->mapToSelect($query, $select);
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $hydrator = $this->getReadAggregate()->getPlatform()->getHydrator($query->getEntityType());

        foreach ($result as $data) {
            $entity = $this->newEntity();
            $hydrator->hydrate($data, $entity);

            $collection->addItem($entity);
        }

        return $collection;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::query()
     */
    public function query($entity = null)
    {
        // TODO Auto-generated method stub

    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::save()
     */
    public function save(\rampage\orm\entity\EntityInterface $entity)
    {
        // TODO Auto-generated method stub

    }





}