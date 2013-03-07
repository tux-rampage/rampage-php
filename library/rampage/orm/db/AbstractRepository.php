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
use rampage\core\data\RestrictableCollectionInterface;

use rampage\orm\RepositoryInterface;
use rampage\orm\ConfigInterface;
use rampage\orm\repository\PersistenceFeatureInterface;
use rampage\orm\exception\RuntimeException;
use rampage\orm\exception\InvalidArgumentException;
use rampage\orm\exception\DomainException;
use rampage\orm\query\Query;
use rampage\orm\query\QueryInterface;

use rampage\orm\db\platform\FieldMapper;
use rampage\orm\db\adapter\AdapterAggregate;
use rampage\orm\db\lazy\CollectionLoadDelegate;
use rampage\orm\db\platform\PlatformInterface;
use rampage\orm\db\platform\hydrator\FieldHydratorInterface;

use rampage\orm\entity\CollectionInterface;
use rampage\orm\entity\EntityInterface;
use rampage\orm\entity\lazy\CollectionInterface as  LazyCollectionInterface;
use rampage\orm\entity\feature\QueryableCollectionInterface;
use rampage\orm\entity\type\EntityType;
use rampage\orm\entity\type\ConfigInterface as EntityTypeConfigInterface;

use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Abstract DB repository
 */
abstract class AbstractRepository implements RepositoryInterface, PersistenceFeatureInterface
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
    protected $adapterName = 'default';

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
     * Entity types
     *
     * @var array
     */
    protected $entityTypes = array();

    /**
     * Id Fields by entity type
     *
     * @var array
     */
    protected $entityTypeIdFields = array();

    /**
     * Entity tables by platform
     *
     * @var array
     */
    protected $entityTables = array();

    /**
     * Entity hydrators by entity and platform
     *
     * @var array
     */
    protected $entityHydrators = array();

    /**
     * Construct
     */
    public function __construct(ObjectManagerInterface $objectManager, ConfigInterface $config, $name = null)
    {
        $this->objectManager = $objectManager;
        $this->setName($name);

        if ($config) {
            $this->setConfig($config);
        }
    }

    /**
     * Entity class name for the given entity type
     *
     * @param string $entityType
     * @return string|null
     */
    protected function getEntityClass($entityType)
    {
        return $this->getEntityType($entityType)->getClass();
    }

    /**
     * New entity instance
     *
     * @return \rampage\orm\entity\EntityInterfaces
     */
    protected function newEntity($type)
    {
        $class = $this->getEntityClass($type);
        if (!$class) {
            throw new DomainException(sprintf('Could not find implementation for entity type "%s".', $type));
        }

        $entity = $this->getObjectManager()->newInstance($class);
        if (!$entity instanceof EntityInterface) {
            throw new RuntimeException(sprintf(
                'Invalid entity implementation for "%s": Must implement rampage\orm\entity\EntityInterface, %s given.',
                $type, (is_object($entity))? get_class($entity) : gettype($entity)
            ));
        }

        return $entity;
    }

    /**
     * Hydrate the given entity with the given data
     *
     * @deprecated
     * @param string $data
     * @param EntityInterface $entity
     * @param string $entityType
     * @return \rampage\orm\db\AbstractRepository
     */
    protected function hydrateEntity(array $data, EntityInterface $entity, $entityType = null)
    {
        return $this;
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
     * Set the entity hydrator
     *
     * @param string $entity
     * @param string $platform
     * @param HydratorInterface $hydrator
     */
    protected function setEntityHydrator($entity, $platform, HydratorInterface $hydrator)
    {
        $entityTypeName = $this->getFullEntityTypeName($entity);
        $platformName = ($platform instanceof PlatformInterface)? $platform->getName() : $platform;

        $this->entityHydrators[$platformName][$entityTypeName] = $hydrator;
        return $this;
    }

    /**
     * Create a new hydrator
     *
     * @param string $entityType
     * @param PlatformInterface $platform
     */
    protected function createHydrator($entityType, PlatformInterface $platform)
    {
        $platformHydrator = $platform->getHydrator($entityType);
        $hydrator = $this->getObjectManager()->newInstance('rampage.orm.db.hydrator.Repository', array(
            'repository' => $this,
            'platformHydrator' => $platformHydrator,
            'mapper' => $platform->getFieldMapper($entityType)
        ));

        return $hydrator;
    }

    /**
     * Returns an entity hydrator
     *
     * @param EntityInterface|EntityType|string $entity
     * @param AdapterAggregate $adapter
     * @return \rampage\orm\db\platform\hydrator\FieldHydratorInterface
     */
    protected function getEntityHydrator($entity, AdapterAggregate $adapter)
    {
        /* @var $platform PlatformInterface */
        $platform = $adapter->getPlatform();
        $entityTypeName = $this->getFullEntityTypeName($entity);
        $platformName = $platform->getName();

        if (isset($this->entityHydrators[$platformName][$entityTypeName])) {
            return $this->entityHydrators[$platformName][$entityTypeName];
        }


        $hydrator = $this->createHydrator($entityTypeName, $platform);
        $this->setEntityHydrator($entity, $platformName, $hydrator);

        return $hydrator;
    }

    /**
     * Create a new adapter aggregate
     *
     * @param string $name
     * @return \rampage\orm\db\adapter\AdapterAggregate
     */
    protected function newAdapterAggregate($name)
    {
        return $this->getObjectManager()->newInstance('rampage.orm.db.adapter.AdapterAggregate', array(
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
     * Returns the entity type instance
     *
     * @param EntityInterface|EntityType|string $name
     * @throws RuntimeException
     * @return \rampage\orm\entity\type\EntityType
     */
    public function getEntityType($name)
    {
        if ($name instanceof EntityType) {
            return $name;
        } else if ($name instanceof EntityInterface) {
            $name = $name->getEntityType();
        }

        if (strpos($name, ':') === false) {
            $name = $this->getName() . ':' . $name;
        }

        if (isset($this->entityTypes[$name])) {
            return $this->entityTypes[$name];
        }

        $config = $this->getConfig();
        if (!$config instanceof EntityTypeConfigInterface) {
            throw new RuntimeException('The current repository config does not implement rampage\orm\entity\type\ConfigInterface');
        }

        $type = new EntityType($name, $this, $config);
        $this->entityTypes[$name] = $type;

        return $type;
    }

    /**
     * Get table name
     *
     * @param EntityType|string $entityType
     * @param PlatformInterface $platform
     */
    protected function getEntityTable($entityType, PlatformInterface $platform)
    {
        $entityType = $this->getFullEntityTypeName($entityType);
        $platformName = $platform->getName();
        if (isset($this->entityTables[$platformName][$entityType])) {
            return $this->entityTables[$platformName][$entityType];
        }

        $table = $platform->getTable($entityType);
        $this->entityTables[$platformName][$entityType] = $table;

        return $table;
    }

    /**
     * ID Fields for the given entity type
     *
     * @param string $entityType
     * @return array
     */
    protected function getEntityTypeIdField($entityType, AdapterAggregate $adapterAggregate)
    {
        /* @var $platform PlatformInterface */
        $platform = $adapterAggregate->getPlatform();
        $entityType = $this->getEntityType($entityType);

        $platformName = $platform->getName();
        $typeName = $entityType->getFullName();

        if (isset($this->entityTypeIdFields[$typeName][$platformName])) {
            return $this->entityTypeIdFields[$typeName][$platformName];
        }

        $identifier = $entityType->getIdentifier();

        if (!$identifier) {
            $this->entityTypeIdFields[$typeName][$platformName] = false;
            return false;
        }

        $mapper = $platform->getFieldMapper($entityType->getFullName());
        if (is_array($identifier)) {
            $fields = array();

            foreach ($identifier as $attribute) {
                $field = $mapper->mapAttribute($attribute);
                $fields[$field] = $field;
            }
        } else {
            $fields = $mapper->mapAttribute($identifier);
        }

        $this->entityTypeIdFields[$typeName][$platformName] = $fields;
        return $fields;
    }

    /**
     * Get the full entity type name including the repository
     *
     * @param EntityType|string $entityType
     * @return string
     */
    protected function getFullEntityTypeName($entityType)
    {
        if ($entityType instanceof EntityType) {
            return $entityType->getFullName();
        }

        return $this->getEntityType($entityType)->getFullName();
    }

    /**
     * Prepare id for object
     *
     * @param EntityInterface|EntityType|string $entityType
     * @param array $data
     * @param FieldMapper $mapper
     * @return string|int
     */
    public function prepareIdForObject($entity, array $data, FieldMapper $mapper)
    {
        $entityType = $this->getEntityType(($entity instanceof EntityInterface)? $entity->getEntityType() : $entity);
        $identifier = $entityType->getIdentifier();

        if (!$identifier) {
            return null;
        }

        if (!is_array($identifier)) {
            $key = $mapper->mapAttribute($identifier);
            $id = (isset($data[$key]))? $data[$key] : null;

            return $id;
        }

        $id = array();
        foreach ($identifier as $attribute) {
            $field = $mapper->mapAttribute($attribute);
            $id[$field] = isset($data[$field])? $data[$field] : null;
        }

        ksort($id);
        $id = base64_encode(json_encode($id));

        return $id;
    }

    /**
     * Prepare ID for DB
     *
     * @param EntityInterface|EntityType|string $entity
     * @param string|int $id
     * @return string|int|array
     */
    protected function prepareIdForDatabase($entity, $id = null)
    {
        if ($entity instanceof EntityInterface) {
            $entityType = $this->getEntityType($entity->getEntityType());
            $id = $entity->getId();
        } else {
            $entityType = $this->getEntityType($entity);
        }

        $identifier = $entityType->getIdentifier();
        if (!$id || !$identifier) {
            return null;
        }

        if (!is_array($identifier)) {
            return $id;
        }

        $id = json_decode(base64_decode($id), true);
        return $id;

    }

    /**
     * Must return the default repository name which is used if this repo is not configured
     *
     * @return string
     */
    abstract protected function getDefaultRepositoryName();

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\RepositoryInterface::getName()
     */
    public function getName()
    {
        if (!$this->name) {
            $this->name = $this->getDefaultRepositoryName();
        }

        return $this->name;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\RepositoryInterface::setName()
     */
    public function setName($name)
    {
        $this->name = ($name === null)? null : (string)$name;
        return $this;
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
     * Config instance
     *
     * @return \rampage\orm\ConfigInterface
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * Get query mapper
     *
     * @return \rampage\orm\db\query\MapperInterface
     */
    protected function getQueryMapper(QueryInterface $query)
    {
        return $this->getObjectManager()->newInstance('rampage.orm.db.query.DefaultMapper', array(
            'repository' => $this,
            'platform' => $this->getReadAggregate()->getPlatform()
        ));
    }

    /**
     * Create a new collection
     *
     * @param QueryInterface $query
     * @return \rampage\orm\entity\LazyLoadableCollection
     */
    protected function newCollection(QueryInterface $query)
    {
        $collection = $this->getObjectManager()->newInstance('rampage.orm.entity.LazyLoadableCollection');
        $itemType = $this->getEntityClass($query->getEntityType());

        if ($itemType && ($collection instanceof RestrictableCollectionInterface)) {
            $collection->restrictItemType($itemType);
        }

        return $collection;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::query()
     */
    public function query($entityType = null)
    {
        /* @var $query Query */
        $query = $this->getObjectManager()->newInstance('rampage.orm.query.Query');
        if ($entityType !== null) {
            $query->setEntityType($this->getFullEntityTypeName($entityType));
        }

        return $query;
    }

    /**
     * Returns the load select
     *
     * @param unknown $id
     * @param unknown $entityType
     * @return Zend\Db\Sql\Select|false
     */
    protected function getLoadSelect($id, $entityType)
    {
        $dbId = $this->prepareIdForDatabase($entityType, $id);

        if (!$dbId) {
            return false;
        }

        $platform = $this->getReadAggregate()->getPlatform();
        $sql = $this->getReadAggregate()->sql();
        $select = $sql->select($this->getEntityTable($entityType, $platform));

        if (!is_array($dbId)) {
            $attribute = $this->getEntityType($entityType)->getIdentifier();
            if (is_array($dbId)) {
                throw new InvalidArgumentException('Invalid identifier');
            }

            $field = $platform->getFieldMapper($entityType)->mapAttribute($attribute);
            $dbId = array($field => $dbId);
        }

        return $select->where($dbId)->limit(1);
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::load()
     */
    public function load($id, $entity)
    {
        if ($entity instanceof EntityInterface) {
            $entityType = $entity->getEntityType();
        } else {
            $entityType = (string)$entity;
            $entity = $this->newEntity($entityType);
        }

        $entityType = $this->getFullEntityTypeName($entityType);
        $select = $this->getLoadSelect($id, $entityType);
        $read = $this->getReadAggregate();

        if (!$select) {
            return false;
        }

        $data = $read->sql()
            ->prepareStatementForSqlObject($select)
            ->execute()
            ->current();

        if (!is_array($data)) {
            return false;
        }

        $this->getEntityHydrator($entityType, $read)->hydrate($data, $entity);
        return $entity;
    }

    /**
     * Returns all entity columns
     *
     * @param EntityType $entityType
     * @param bool $excludeIdentifiers
     */
    protected function getEntityColumns($entityType, $excludeIdentifiers = false)
    {
        $entityType = $this->getEntityType($entityType);
        $write = $this->getWriteAggregate();
        $platform = $write->getPlatform();
        $identifiers = $this->getEntityTypeIdField($entityType, $write);

        $columns = $write->metadata()->getColumnNames($this->getEntityTable($entityType, $platform));
        $columns = array_combine($columns, $columns);

        if (!$excludeIdentifiers || !$identifiers) {
            return $columns;
        }

        if (!is_array($identifiers)) {
            $identifiers = array($identifiers);
        }

        foreach ($identifiers as $field) {
            if (isset($columns[$field])) {
                unset($columns[$field]);
            }
        }

        return $columns;
    }

    /**
     * Get write hydrator for the given entity
     *
     * @param EntityType|string $entityType
     * @param string $excludeIdentifiers
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    protected function getEntityWriteHydrator($entityType, $excludeIdentifiers = false)
    {
        $hydrator = $this->getEntityHydrator($entityType, $this->getWriteAggregate());

        if (($hydrator instanceof FieldHydratorInterface)) {
            $hydrator->setAllowedFields($this->getEntityColumns($entityType, $excludeIdentifiers));
        }

        return $hydrator;
    }

    /**
     * Prepare id for where in sql objects
     *
     * @param AdapterAggregate $adapter
     * @param EntityInterface|EntityType|string $entity
     * @param string $id The identifier value. May be omitted whem $entity implements EntityInterface
     * @throws InvalidArgumentException
     */
    protected function prepareIdForWhere(AdapterAggregate $adapter, $entity, $id = null)
    {
        $entityType = $this->getEntityType($entity);
        $where = $this->prepareIdForDatabase($entity, $id);

        if ($where === null) {
            return null;
        }

        if (!is_array($where)) {
            $field = $this->getEntityTypeIdField($entityType, $adapter);
            if (is_array($field)) {
                throw new InvalidArgumentException('Invalid identifier');
            }

            $where = array($field => $id);
        }

        return $where;
    }

    /**
     * Create sql object for inserting entity data
     *
     * @param EntityInterface $entity
     * @return \Zend\Db\Sql\PreparableSqlInterface
     */
    protected function createInsertSqlObject(EntityInterface $entity)
    {
        $entityType = $this->getEntityType($entity->getEntityType());
        $platform = $this->getWriteAggregate()->getPlatform();
        $hydrator = $this->getEntityWriteHydrator($entityType, $platform->getCapabilities()->supportsAutomaticIdentities());
        $data = $hydrator->extract($entity);

        if (!is_array($data) || empty($data)) {
            return false;
        }

        $platform = $this->getWriteAggregate()->getPlatform();
        $sql = $this->getWriteAggregate()->sql();
        $insert = $sql->insert($this->getEntityTable($entityType, $platform));
        $insert->values($data);

        return $insert;
    }

    /**
     * Create sql object for updating entity data
     *
     * @param EntityInterface $entity
     * @param int|string|array $id
     */
    protected function createUpdateSqlObject(EntityInterface $entity)
    {
        $entityType = $this->getEntityType($entity->getEntityType());
        $hydrator = $this->getEntityWriteHydrator($entityType, true);
        $data = $hydrator->extract($entity);

        if (!is_array($data) || empty($data)) {
            return false;
        }

        $write = $this->getWriteAggregate();
        $platform = $write->getPlatform();
        $sql = $write->sql();
        $update = $sql->update($this->getEntityTable($entityType, $platform));
        $where = $this->prepareIdForWhere($write, $entity);

        if (!$where) {
            return false;
        }

        $update->set($data)->where($where, PredicateSet::OP_AND);

        return $update;
    }

    /**
     * Returns the delete sql object
     *
     * @param EntityInterface $entity
     */
    protected function createDeleteSqlObject(EntityInterface $entity)
    {
        $write = $this->getWriteAggregate();
        $where = $this->prepareIdForWhere($write, $entity);

        if (!$where) {
            return false;
        }

        $sql = $write->sql();
        $delete = $sql->delete($this->getEntityTable($entity->getEntityType(), $write->getPlatform()));

        return $delete->where($where);
    }

    /**
     * Prepare a generated result
     *
     * @param unknown $entityType
     */
    protected function prepareGeneratedValue($entityType)
    {
        // TODO
    }

    /**
     * Fetch the generated value
     *
     * @param ResultInterface $result
     * @param EntityInterface|string $entity
     * @param string $preparedValue
     */
    protected function fetchGeneratedValue(ResultInterface $result, $entityType, $preparedValue)
    {
        // TODO
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::save()
     */
    public function save(EntityInterface $entity)
    {
        $updateId = false;
        $preparedValue = null;

        if (!$entity->getId()) {
            $action = $this->createInsertSqlObject($entity, $preparedValue);
            $updateId = $this->getEntityType($entity)->usesGeneratedId();
        } else {
            $action = $this->createUpdateSqlObject($entity);
        }

        if (!$action) {
            return $this;
        }

        $result = $this->getWriteAggregate()
            ->sql()
            ->prepareStatementForSqlObject($action)
            ->execute();

        if ($updateId) {
            $entity->setId($this->fetchGeneratedValue($result, $entity, $preparedValue));
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::delete()
     */
    public function delete(EntityInterface $entity)
    {
        $delete = $this->createDeleteSqlObject($entity);

        if (!$delete) {
            return $this;
        }

        $this->getWriteAggregate()
            ->sql()
            ->prepareStatementForSqlObject($delete)
            ->execute();

        return $this;
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
        $entityType = $this->getFullEntityTypeName($query->getEntityType());

        $mapper->mapToSelect($query, $select);
        $result = $sql->prepareStatementForSqlObject($select)->execute();

        foreach ($result as $data) {
            $entity = $this->newEntity($entityType);

            $this->getEntityHydrator($entityType, $this->getReadAggregate())->hydrate($data, $entity);
            $collection->addItem($entity);
        }

        return $collection;
    }
}
