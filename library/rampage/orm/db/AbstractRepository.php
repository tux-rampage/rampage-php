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
use rampage\core\Utils;
use rampage\core\data\ArrayExchangeInterface;

use rampage\orm\ConfigInterface;
use rampage\orm\hydrator\EntityHydrator;

use rampage\orm\repository\PersistenceFeatureInterface;
use rampage\orm\repository\CursorProviderInterface;

use rampage\orm\exception\RuntimeException;
use rampage\orm\exception\InvalidArgumentException;
use rampage\orm\exception\DomainException;

use rampage\orm\query\Query;
use rampage\orm\query\QueryInterface;

use rampage\orm\db\adapter\AdapterAggregate;
use rampage\orm\db\lazy\CollectionLoadDelegate;
use rampage\orm\db\platform\PlatformInterface;
use rampage\orm\db\platform\SequenceSupportInterface;

use rampage\orm\entity\CollectionInterface;
use rampage\orm\entity\EntityInterface;
use rampage\orm\entity\lazy\CollectionInterface as  LazyCollectionInterface;
use rampage\orm\entity\feature\QueryableCollectionInterface;
use rampage\orm\entity\type\EntityType;
use rampage\orm\entity\type\ConfigInterface as EntityTypeConfigInterface;
use rampage\orm\entity\feature\NewItemInterface;

// ZF dependencies
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Db\Sql\Expression as SQLExpression;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

// Event framework
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;

/**
 * Abstract DB repository
 */
abstract class AbstractRepository implements RepositoryInterface,
    PersistenceFeatureInterface,
    CursorProviderInterface,
    EventManagerAwareInterface
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
     * Write adapter
     *
     * @var \rampage\orm\db\adapter\AdapterAggregate
     */
    private $adapterAggregate = null;

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
     * Event manager instance
     *
     * @var EventManagerInterface
     */
    private $eventManager = null;

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
     * Must return the default repository name which is used if this repo is not configured
     *
     * @return string
     */
    abstract protected function getDefaultRepositoryName();

    /**
     * @see \Zend\EventManager\EventManagerAwareInterface::setEventManager()
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $instanceClass = get_class($this);

        $eventManager->setIdentifiers(array(
            'rampage.repository.database',
            strtr($instanceClass, '\\', '.'),
            strtr(__CLASS__, '\\', '.')
        ));

        $this->eventManager = $eventManager;
        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\EventManager\EventsCapableInterface::getEventManager()
     */
    public function getEventManager()
    {
        if (!$this->eventManager) {
            $this->setEventManager(new EventManager());
        }

        return $this->eventManager;
    }

    /**
     * Returns the event
     *
     * @param string $name
     * @return \rampage\orm\db\Event
     */
    protected function getEvent($name, $target = null)
    {
        $event = new Event($this, $name, $target);
        return $event;
    }

	/**
     * Retruns the transaction for writing
     *
     * @return \rampage\db\driver\feature\TransactionFeatureInterface
     */
    protected function getTransaction()
    {
        return $this->getAdapterAggregate()->getTransactionFeature();
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
    protected function newEntity($type, $ensureType = null)
    {
        $class = $this->getEntityClass($type);
        if (!$class) {
            throw new DomainException(sprintf('Could not find implementation for entity type "%s".', $type));
        }

        $entity = $this->getObjectManager()->newInstance($class);
        if (!$ensureType) {
            return $entity;
        }

        $ensureType = strtr($ensureType, '.', '\\');
        if (!$entity instanceof $ensureType) {
            throw new RuntimeException(sprintf(
                'Invalid entity implementation for "%s": Must implement %s, %s given.',
                $type, strtr($ensureType, '\\', '.'),
                (is_object($entity))? strtr(get_class($entity), '\\', '.') : gettype($entity)
            ));
        }

        return $entity;
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
     * @param HydratorInterface $hydrator
     */
    protected function setEntityHydrator($entity, HydratorInterface $hydrator)
    {
        $entityTypeName = $this->getFullEntityTypeName($entity);
        $this->entityHydrators[$entityTypeName] = $hydrator;
        return $this;
    }

    /**
     * Create a new hydrator
     *
     * @param string $entityType
     * @param PlatformInterface $platform
     * @return \rampage\orm\hydrator\EntityHydrator
     */
    protected function createHydrator($entityType)
    {
        $hydrator = $this->getAdapterAggregate()
            ->getPlatform()
            ->getHydrator($entityType);

        $proxy = $this->getObjectManager()->newInstance('rampage.orm.hydrator.EntityHydrator', array(
            'repository' => $this,
            'entityType' => $this->getEntityType($entityType)
        ));

        if (!$proxy instanceof EntityHydrator) {
            throw new RuntimeException();
        }

        $proxy->setHydratorStrategy($hydrator);
        return $proxy;
    }

    /**
     * Returns an entity hydrator
     *
     * @param EntityInterface|EntityType|string $entityType
     * @param AdapterAggregate $adapter
     * @return \rampage\orm\hydrator\EntityHydrator
     */
    protected function getEntityHydrator($entityType)
    {
        $entityTypeName = $this->getFullEntityTypeName($entityType);

        if (isset($this->entityHydrators[$entityTypeName])) {
            return $this->entityHydrators[$entityTypeName];
        }

        $hydrator = $this->createHydrator($entityTypeName);
        $this->setEntityHydrator($entityType, $hydrator);

        return $hydrator;
    }

    /**
     * Create a new adapter aggregate
     *
     * @param string $name
     * @return \rampage\orm\db\adapter\AdapterAggregate
     */
    protected function createAdapterAggregate($name)
    {
        return $this->getObjectManager()->newInstance('rampage.orm.db.adapter.AdapterAggregate', array(
            'adapterName' => $name
        ));
    }

    /**
     * Creates a module setup instance
     *
     * @param string $moduleName
     * @return \rampage\orm\db\ModuleSetup
     */
    protected function createModuleSetup($moduleName, $resourceName = null)
    {
        $setup = $this->getObjectManager()->newInstance('rampage.orm.db.ModuleSetup', array(
            'adapterAggregate' => $this->getAdapterAggregate()
        ));

        if (!$setup instanceof ModuleSetup) {
            throw new RuntimeException(sprintf(
                'Invalid module setup instance. Expected rampage.orm.db.ModuleSetup, %s given',
                is_object($setup)? strtr(get_class($setup), '\\', '.') : gettype($setup)
            ));
        }

        $resourceName = ($resourceName)?: $this->getName();

        $setup->setModuleName($moduleName);
        $setup->setName($resourceName);

        return $setup;
    }

    /**
     * Read adapter aggregate
     *
     * @return \rampage\orm\db\adapter\AdapterAggregate
     */
    protected function getAdapterAggregate()
    {
        if ($this->adapterAggregate) {
            return $this->adapterAggregate;
        }

        $aggregate = $this->createAdapterAggregate($this->adapterName);
        $this->setAdapterAggregate($aggregate);

        return $aggregate;
    }

    /**
     * Set the adapter aggregate
     *
     * @param \rampage\orm\db\adapter\AdapterAggregate $read
     */
    public function setAdapterAggregate(AdapterAggregate $aggregate)
    {
        $this->adapterAggregate = $aggregate;
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
     * Get table name for the given entity
     *
     * @param EntityInterface|EntityType|string $entityType
     * @return string
     */
    protected function getEntityTable($entityType)
    {
        $entityType = $this->getFullEntityTypeName($entityType);
        if (isset($this->entityTables[$entityType])) {
            return $this->entityTables[$entityType];
        }

        $table = $this->getAdapterAggregate()
            ->getPlatform()
            ->getTable($entityType);

        $this->entityTables[$entityType] = $table;
        return $table;
    }

    /**
     * Returns the field mapper
     *
     * @param string $entityType
     * @return \rampage\orm\db\platform\FieldMapper
     */
    protected function getFieldMapper($entityType)
    {
        $entityType = $this->getFullEntityTypeName($entityType);

        return $this->getAdapterAggregate()
                    ->getPlatform()
                    ->getFieldMapper($entityType);
    }

    /**
     * Returns all entity columns
     *
     * @param EntityType $entityType
     * @return array
     */
    private function getEntityColumns($entityType)
    {
        $table = $this->getEntityTable($entityType);
        $columns = $this->getAdapterAggregate()
            ->metadata()
            ->getColumnNames($table);

        $columns = array_combine($columns, $columns);
        if (!is_array($columns)) {
            throw new RuntimeException('Failed to resolve column for ' . $entityType->getFullName());
        }

        return $columns;
    }

    /**
     * Map database data to object data
     *
     * @param array $data
     * @return array
     */
    protected function mapDataForObject($data, $entityType)
    {
        if (!is_array($data) && !($data instanceof \Traversable)) {
            throw new InvalidArgumentException('Data must be an array or implement the Traversable interface');
        }

        $result = array();
        $mapper = $this->getFieldMapper($entityType);

        foreach ($data as $field => $value) {
            $attribute = $mapper->mapField($field);
            $result[$attribute] = $value;
        }

        return $result;
    }

    /**
     * Map object data for database
     *
     * @param array $data
     * @param unknown $entityType
     */
    protected function mapDataForDatabase($data, $entityType)
    {
        if (!is_array($data) && !($data instanceof \Traversable)) {
            throw new InvalidArgumentException('Data must be an array or implement the Traversable interface');
        }

        $result = array();
        $mapper = $this->getFieldMapper($entityType);
        $fields = $this->getEntityColumns($entityType);

        foreach ($data as $attribute => $value) {
            $field = $mapper->mapField($attribute);

            if (in_array($field, $fields)) {
                $result[$field] = $value;
            }
        }

        return $result;
    }

    /**
     * Get the full entity type name including the repository
     *
     * @param EntityInterface|EntityType|string $entityType
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
     * Extract identifiers from data array
     *
     * @param \ArrayAccess|array $data
     * @param EntityInterface|EntityType|string $entityType
     * @return array
     */
    protected function extractIdFromData($data, $entityType)
    {
        $entityType = $this->getEntityType($entityType);
        $identifier = $entityType->getIdentifier();

        if ($identifier->isUndefined()) {
            return null;
        }

        $missing = array();
        $id = array();

        /* @var $attribute \rampage\orm\entity\type\Attribute */
        foreach ($identifier as $attribute) {
            $name = $attribute->getName();

            if (!isset($data[$name])) {
                $missing[$name] = null;
                continue;
            }

            $id[$name] = $data[$name];
        }

        if (empty($id)) {
            return null;
        }

        $id += $missing;
        return $id;
    }

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
            'platform' => $this->getAdapterAggregate()->getPlatform()
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
     * @param object $object
     * @param array|string|int $id
     * @param EntityInterface|EntityType|string $entityType
     * @return Zend\Db\Sql\Select|false
     */
    protected function getLoadSelect($object, $id, $entityType)
    {
        $entityType = $this->getEntityType($entityType);

        if (!is_array($id) && !($id instanceof \Traversable)) {
            if ($entityType->getIdentifier()->isMultiAttribute()) {
                throw new InvalidArgumentException('Invalid identifier "%s": Multiple attributes required.', $id);
            }

            $attribute = $entityType->getIdentifier()->getAttribute()->getName();
            $id = array($attribute => $id);
        }

        $where = $this->mapDataForDatabase($id, $entityType);
        if (!$where) {
            return false;
        }

        $select = $this->getAdapterAggregate()
                       ->sql()
                       ->select($this->getEntityTable($entityType))
                       ->where($where)
                       ->limit(1);

        return $select;
    }

    /**
     * Load the specified entity
     *
     * @param int $id
     * @param EntityInterface|string $entity
     * @return \rampage\orm\entity\EntityInterface|false
     */
    protected function loadEntity($id, $entity)
    {
        $entityType = $this->getEntityType($entity);

        if (!$entity instanceof EntityInterface) {
            $entity = $this->newEntity($entityType);
        }

        if (!$this->loadObject($entity, $id, $entityType)) {
            return false;
        }

        return $entity;
    }

    /**
     * Load object
     *
     * @param object $object
     * @param mixed $id
     * @param EntityType|string $entityType
     * @return boolean|object
     */
    protected  function loadObject($object, $id, $entityType)
    {
        $select = $this->getLoadSelect($object, $id, $entityType);
        if (!$select) {
            return false;
        }

        $data = $this->getAdapterAggregate()
            ->sql()
            ->prepareStatementForSqlObject($select)
            ->execute()
            ->current();

        if (!$data) {
            return false;
        }

        $data = $this->mapDataForObject($data, $entityType);
        $hydrator = $this->getEntityHydrator($entityType);

        $hydrator->hydrate($data, $object);
        return true;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::load()
     */
    public function load($id, $entity)
    {
        $method = $this->getEntityPersistenceMethod('load', $entity);
        $events = $this->getEventManager();

        $events->trigger($this->getEvent(Event::LOAD_BEFORE, $entity));

        if (method_exists($this, $method)) {
            $result = $this->$method($id, $entity);
        } else {
            $result = $this->loadEntity($id, $entity);
        }

        $entity = ($result !== false)? $result : $entity;
        $events->trigger($this->getEvent(Event::LOAD_AFTER, $entity)->setParam('success', ($result !== false)));

        return $result;
    }

    /**
     * Checks if updating the id values is allowed
     *
     * The default implementation will always return false.
     * Overwrite this method allow updating id values for specific entities
     *
     * @param EntityInterface|EntityType|string $entityType
     * @return boolean
     */
    protected function isIdUpdateAllowed($entityType)
    {
        return false;
    }

    /**
     * Create sql object for inserting entity data
     *
     * @param object $object
     * @package array $data
     * @params EntityInterface|EntityType|string $entityType
     * @return \Zend\Db\Sql\PreparableSqlInterface
     */
    protected function createInsertSqlObject($object, $data, $entityType)
    {
        $entityType = $this->getEntityType($entityType);
        $data = $this->mapDataForDatabase($data, $entityType);

        if (!$data) {
            return false;
        }

        // Build insert sql object
        $table = $this->getEntityTable($entityType);
        $insert = $this->getAdapterAggregate()
            ->sql()
            ->insert($table)
            ->values($data);

        return $insert;
    }

    /**
     * Create sql object for updating entity data
     *
     * @param object $object
     * @param array $data
     * @param EntityType|string $entityType
     * @param array $id
     * @return \Zend\Db\Sql\PreparableSqlInterface|false
     */
    protected function createUpdateSqlObject($object, $data, $entityType, array $id = null)
    {
        $entityType = $this->getEntityType($entityType);
        $id = ($id)?: $this->extractIdFromData($data, $entityType);
        $where = $this->mapDataForDatabase($id, $entityType);

        if (!$where) {
            return false;
        }

        if (!$this->isIdUpdateAllowed($entityType)) {
            foreach ($entityType->getIdentifier() as $attribute) {
                unset($data[$attribute->getName()]);
            }
        }

        $set = $this->mapDataForDatabase($data, $entityType);
        if (empty($set)) {
            return false;
        }

        $table = $this->getEntityTable($entityType);
        $update = $this->getAdapterAggregate()
            ->sql()
            ->update($table)
            ->set($set)
            ->where($where, PredicateSet::OP_AND);

        return $update;
    }

    /**
     * Returns the delete sql object
     *
     * @param object $object
     * @param array $data
     */
    protected function createDeleteSqlObject($object, $entityType, array $id = null)
    {
        $data = $this->getEntityHydrator($entityType)->extract($object);
        $id = ($id)?: $this->extractIdFromData($data, $entityType);
        $where = $this->mapDataForDatabase($id, $entityType);

        if (!$where) {
            return false;
        }

        $table = $this->getEntityTable($entityType);
        $delete = $this->getAdapterAggregate()
            ->sql()
            ->delete($table)
            ->where($where);

        return $delete;
    }

    /**
     * Prepare a generated id value
     *
     * Some DBMS don't support automatically inserting id values (i.e. Oracle).
     * In this cases the value must be retrieved from a sequence first.
     *
     * This method should be used before performing the insert.
     *
     * This method will return NULL when the DBMS supports auto increment/identity
     *
     * @param EntityInterface|EntityType|string $entityType
     * @return int|string|null The generated id if required or null if the DBMS supports auto increment/identity
     */
    protected function prepareGeneratedValue($entityType)
    {
        $adapterAggregate = $this->getAdapterAggregate();
        $platform = $adapterAggregate->getPlatform();

        if ($platform->getCapabilities()->supportsAutomaticIdentities()) {
            return null;
        }

        if (!$platform instanceof SequenceSupportInterface) {
            throw new DomainException('The current platform does not support auto identy columns or sequences');
        }

        return $platform->fetchNextSequenceId($adapterAggregate->getAdapter(), $this->getFullEntityTypeName($entityType));
    }

    /**
     * Fetch the generated id value
     *
     * This will fetch the last auto generated value from result set when the DBMS supports it.
     * Otherwise $preparedValue will ber returned.
     *
     * $preparedValue should be the result from a call of {@link prepareGeneratedValue()}
     *
     * @param ResultInterface $result The query result to use for fetching the generated value
     * @param AdapterAggregate $adapterAggregate The adapter aggregate to use (Defaults to the write aggregate)
     * @param int|string|null $preparedValue The prepared sequence value if the DBMS doesn't support auto increment
     */
    protected function fetchGeneratedValue(ResultInterface $result, $preparedValue)
    {
        $capabilities = $this->getAdapterAggregate()
            ->getPlatform()
            ->getCapabilities();

        if (!$capabilities->supportsAutomaticIdentities()) {
            if ($preparedValue === null) {
                throw new InvalidArgumentException('The current platform does not support auto identity values. The pre-generated value must not be NULL in this case.');
            }

            return $preparedValue;
        }

        return $result->getGeneratedValue();
    }

    /**
     * Save an entity
     *
     * @param EntityInterface $entity
     */
    protected function saveEntity(EntityInterface $entity)
    {
        $this->saveObject($entity, $entity);
        return $this;
    }

    /**
     * Check if id matches the identifier
     *
     * @param string|array $id
     * @param EntityType $entityType
     * @return bool
     */
    private function checkValueMatchesIdentifier($id, EntityType $entityType)
    {
        /* @var $identifier \rampage\orm\entity\type\Identifier */
        $identifier = $entityType->getIdentifier();

        if (!is_array($id)) {
            return !$identifier->isMultiAttribute();
        }

        $expected = $identifier->getAttributeNames();
        $actual = array_keys($id);

        if (count($expected) != count($actual)) {
            return false;
        }

        sort($expected);
        sort($actual);
        $diff = array_diff($expected, $actual);

        return empty($diff);
    }

    /**
     * Check if there are records for the given id
     *
     * @param string $id
     * @param EntityType|string $entityType
     */
    private function hasRecordsForId($id, $entityType)
    {
        $entityType = $this->getEntityType($entityType);

        if (!is_array($id)) {
            $identifier = $entityType->getIdentifier();
            if ($identifier->isMultiAttribute() || $identifier->isUndefined()) {
                throw new InvalidArgumentException('Invalid identifier: array expected');
            }

            $id = array($identifier->getAttribute()->getName() => $id);
        }

        $where = $this->mapDataForDatabase($id, $entityType);
        if (!$where) {
            return false;
        }

        $table = $this->getEntityTable($entityType);
        $sql = $this->getAdapterAggregate()->sql();
        $select = $sql->select($table)
            ->columns(array('numrows' => new SQLExpression('COUNT(*)')))
            ->where($where);

        $result = $sql->prepareStatementForSqlObject($select)->execute()->current();
        if (!$result || !isset($result['numrows'])) {
            return false;
        }

        return ($result['numrows'] > 0);
    }

    /**
     * Check if object should be a new record
     *
     * @param string $object
     * @param int|string|array $id
     * @param EntityType $entityType
     * @return boolean
     */
    protected function isObjectNew($object, $id, $entityType)
    {
        if ($object instanceof NewItemInterface) {
            return $object->isNewItem();
        }

        $entityType = $this->getEntityType($entityType);
        if ($entityType->usesGeneratedId() && $this->checkValueMatchesIdentifier($id, $entityType)) {
            return false;
        }

        return !$this->hasRecordsForId($id, $entityType);
    }

    /**
     * Hydrate the given id value into the given object
     *
     * @param object $object
     * @param string $attribute
     * @param string $id
     * @param EntityType|string $entityType
     */
    protected function hydrateIdValue($object, $attribute, $id, $entityType)
    {
        if ($object instanceof ArrayExchangeInterface) {
            $object->add(array($attribute => $id));
            return $this;
        }

        $property = Utils::camelize($attribute);
        $method = 'set' . $property;

        if (is_callable(array($object, $method))) {
            $object->$method($id);
            return $this;
        }

        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty(lcfirst($property));

        $property->setAccessible(true);
        $property->setValue($object, $id);

        return $this;
    }

    /**
     * Save a value object
     *
     * @param object $object
     * @param EntityInterface|EntityType|string $entityType
     */
    protected function saveObject($object, $entityType)
    {
        $entityType = $this->getEntityType($entityType);
        $transaction = $this->getTransaction();
        $aggregate = $this->getAdapterAggregate();
        $data = $this->getEntityHydrator($entityType)->extract($object);
        $id = $this->extractIdFromData($data, $entityType);

        try {
            $transaction->start();

            $addIdAttribute = false;
            $preparedIdValue = null;
            $capabilities = $this->getAdapterAggregate()->getPlatform()->getCapabilities();

            if (!$id || $this->isObjectNew($object, $id, $entityType)) {
                if ($entityType->usesGeneratedId()) {
                    $addIdAttribute = $entityType->getIdentifier()->getAttribute()->getName();
                    $preparedIdValue = $this->prepareGeneratedValue($entityType);

                    if ($capabilities->supportsAutomaticIdentities()) {
                        unset($data[$addIdAttribute]);
                    } else {
                        $data[$addIdAttribute] = $preparedIdValue;
                    }
                }

                $action = $this->createInsertSqlObject($object, $data, $entityType);
            } else {
                $action = $this->createUpdateSqlObject($object, $data, $entityType, $id);
            }

            if ($action) {
                $result = $this->getAdapterAggregate()
                    ->sql()
                    ->prepareStatementForSqlObject($action)
                    ->execute();

                if ($addIdAttribute) {
                    $id = $this->fetchGeneratedValue($result, $preparedIdValue);
                    $this->hydrateIdValue($object, $addIdAttribute, $id, $entityType);
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }

        return $this;
    }

    /**
     * Returns the entity specific persistence method
     *
     * @param string $type
     * @param string $entity
     * @return string
     */
    protected function getEntityPersistenceMethod($type, $entity)
    {
        $translate = array(
            '.' => '_',
            ':' => '_',
            ' ' => ''
        );

        $entityType = $this->getEntityType($entity)->getUnqualifiedName();
        $method = $type . Utils::camelize(strtr($entityType, $translate)) . 'Entity';

        return $method;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::save()
     */
    public function save(EntityInterface $entity)
    {
        $events = $this->getEventManager();
        $method = $this->getEntityPersistenceMethod('save', $entity);

        $events->trigger($this->getEvent(Event::SAVE_BEFORE, $entity));

        if (method_exists($this, $method)) {
            $this->$method($entity);
        } else {
            $this->saveEntity($entity);
        }

        $events->trigger($this->getEvent(Event::SAVE_AFTER, $entity));
        return $this;
    }

    /**
     * Delete an entity from persistence
     *
     * @param EntityInterface $entity
     */
    protected function deleteEntity(EntityInterface $entity)
    {
        $this->deleteObject($entity, $this->getEntityType($entity));
        return $this;
    }

    /**
     * Delete an object from persistence
     *
     * @param object $object
     * @param EntityType|entity $entityType
     */
    protected function deleteObject($object, $entityType, array $id = null)
    {
        $transaction = $this->getWriteTransaction();
        $delete = $this->createDeleteSqlObject($object, $entityType, $id);

        if (!$delete) {
            return $this;
        }

        try {
            $transaction->start();
            $this->getAdapterAggregate()
                ->sql()
                ->prepareStatementForSqlObject($delete)
                ->execute();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::delete()
     */
    public function delete(EntityInterface $entity)
    {
        $method = $this->getEntityPersistenceMethod('delete', $entity);
        $events = $this->getEventManager();

        $events->trigger($this->getEvent(Event::DELETE_BEFORE, $entity));

        if (method_exists($this, $method)) {
            $this->$method($entity);
        } else {
            $this->deleteEntity($entity);
        }

        $events->trigger($this->getEvent(Event::DELETE_AFTER, $entity));
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
        $sql = $this->getAdapterAggregate()->sql();
        $select = $this->getQueryMapper($query)->mapToSizeSelect($query, $sql->select());
        $result = $sql->prepareStatementForSqlObject($select)->execute()->current();

        $size = ($result && isset($result['size']))? (int)$result['size'] : 0;
        $collection->setSize($size);

        return $this;
    }

    /**
     * Create a new collection item
     *
     * @param string $entityType
     * @param string $class
     * @return \rampage\orm\ValueObjectInterface
     */
    protected function newCollectionItem($entityType, $class = null)
    {
        if (!$class) {
            return $this->newEntity($entityType);
        }

        return $this->getObjectManager()->newInstance($class);
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::loadCollection()
     */
    public function loadCollection(CollectionInterface $collection, QueryInterface $query, $itemClass = null)
    {
        $mapper = $this->getQueryMapper($query);
        $sql = $this->getAdapterAggregate()->sql();
        $entityType = $this->getFullEntityTypeName($query->getEntityType());
        $select = $mapper->mapToSelect($query, $sql->select());
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $hydrator = $this->getEntityHydrator($entityType);

        foreach ($result as $data) {
            $data = $this->mapDataForObject($data, $entityType);
            $entity = $this->newCollectionItem($entityType, $itemClass);

            $hydrator->hydrate($data, $entity);
            $collection->addItem($entity);
        }

        return $collection;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\CursorProviderInterface::getForwardCursor()
     */
    public function getForwardCursor(QueryInterface $query, $itemClass = null)
    {
        $mapper = $this->getQueryMapper($query);
        $sql = $this->getAdapterAggregate()->sql();
        $entityType = $this->getFullEntityTypeName($query->getEntityType());
        $select = $mapper->mapToSelect($query, $sql->select());
        $result = $sql->prepareStatementForSqlObject($select)->execute();

        // Build the item factory
        $hydrator = $this->getEntityHydrator($entityType);
        $objectManager = $this->getObjectManager();
        $fieldMapper = $this->getFieldMapper($entityType);

        if (!$itemClass) {
            $itemClass = $this->getEntityClass($entityType);
        }

        // TODO: Move to invokable instead of using a closure
        $factory = function(array $data) use ($hydrator, $itemClass, $objectManager, $fieldMapper) {
            $item = $objectManager->newInstance($itemClass);
            $objectData = array();

            foreach ($data as $field => $value) {
                $attribute = $fieldMapper->mapField($field);
                $objectData[$attribute] = $value;
            }

            $hydrator->hydrate($data, $item);
            return $item;
        };

        $cursor = new ForwardCursor($result, $factory);
        return $cursor;
    }
}
