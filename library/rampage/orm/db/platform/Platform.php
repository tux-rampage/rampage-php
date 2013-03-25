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

namespace rampage\orm\db\platform;

use rampage\orm\exception\DependencyException;
use rampage\orm\exception\RuntimeException;
use rampage\core\ObjectManagerInterface;

use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Db\Adapter\Platform\PlatformInterface as AdapterPlatformInterface;

/**
 * Default Platform
 */
class Platform implements PlatformInterface
{
    /**
     * Platform name
     *
     * @var string
     */
    private $name = null;

    /**
     * Config instance
     *
     * @var \rampage\orm\db\platform\ConfigInterface
     */
    private $config = null;

    /**
     * Object manager
     *
     * @var \rampage\core\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Current field mappers
     *
     * @var array
     */
    private $fieldMappers = array();

    /**
     * Entity table name mapping
     *
     * @var array
     */
    protected $tables = array();

    /**
     * Hydrator instances per entity
     *
     * @var array
     */
    private $hydrators = array();

    /**
     * Adapter platform
     *
     * @var \Zend\Db\Adapter\Platform\PlatformInterface
     */
    private $adapterPlatform = null;

    /**
     * DDL Renderer
     *
     * @var DDLRenderer
     */
    private $ddlRenderer = null;

    /**
     * Entity table name replacements
     *
     * @var string
     */
    private $entityTableReplacements = array(
        '.' => '_',
        ':' => '_',
        '-' => '_',
        ' ' => '',
    );

    /**
     * Platform capabilities
     *
     * @var PlatformCapabilities
     */
    private $capabilities = null;

    /**
     * Create platform
     *
     * @param ConfigInterface $config
     */
    public function __construct(ObjectManagerInterface $objectmanager, ConfigInterface $config, AdapterPlatformInterface $adapterPlatform)
    {
        $this->config = $config;
        $this->objectManager = $objectmanager;
        $this->adapterPlatform = $adapterPlatform;
    }

    /**
     * Platform name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

	/**
     * Platform name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string)$name;
        return $this;
    }

    /**
     * Returns the adapter platform
     *
     * @return \Zend\Db\Adapter\Platform\PlatformInterface
     */
    public function getAdapterPlatform()
    {
        return $this->adapterPlatform;
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
     * Config instance
     *
     * @return \rampage\orm\db\platform\ConfigInterface
     */
    protected function getConfig()
    {
        if (!$this->config) {
            throw new DependencyException('Missing platform config instance');
        }

        return $this->config;
    }

    /**
     * Canonicalie entity name
     *
     * @param string $name
     * @return string
     */
    protected function canonicalizeEntityResourceName($name)
    {
        $name = strtolower($name);
        return $name;
    }

    /**
     * Create field mapper
     *
     * @param string $resourceName
     * @return \rampage\orm\db\platform\FieldMapper
     */
    protected function createFieldMapper($resourceName)
    {
        return new FieldMapper();
    }

    /**
     * Format entity to table name
     *
     * @param string $resourceName
     * @return string
     */
    protected function formatResourceToTableName($resourceName)
    {
        $table = strtr($resourceName, $this->entityTableReplacements);
        $table = $this->formatIdentifier($table);

        return $table;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\PlatformInterface::formatIdentifier()
     */
    public function formatIdentifier($identifier)
    {
        return strtolower($identifier);
    }

    /**
     * Returns the table name
     *
     * @param string $resourceName
     */
    public function getTable($resourceName)
    {
        $entitiy = $this->canonicalizeEntityResourceName($resourceName);
        $table = $this->getConfig()->getTable($this, $resourceName);

        if (!$table) {
            $table = $this->formatResourceToTableName($resourceName);
        }

        return $table;
    }

    /**
     * Set the an entity field mapper
     *
     * @param string $resourceName
     * @param FieldMapper $mapper
     * @return \rampage\orm\db\platform\DefaultPlatform
     */
    public function setFieldMapper($resourceName, FieldMapper $mapper)
    {
        $resourceName = $this->canonicalizeEntityResourceName($resourceName);
        $this->fieldMappers[$resourceName] = $mapper;

        return $this;
    }

    /**
     * Get the field mapper for the given entity
     *
     * @param string $resourceName
     * @return FieldMapper
     */
    public function getFieldMapper($resourceName)
    {
        $resourceName = $this->canonicalizeEntityResourceName($resourceName);
        if (isset($this->fieldMappers[$resourceName])) {
            return $this->fieldMappers[$resourceName];
        }

        $mapper = $this->createFieldMapper($resourceName);
        $this->getConfig()->configureFieldMapper($mapper, $this, $resourceName);

        $this->setFieldMapper($resourceName, $mapper);
        return $mapper;
    }

    /**
     * Set the hydrator
     *
     * @param string $resourceName
     * @param HydratorInterface $hydrator
     * @return \rampage\orm\db\platform\DefaultPlatform
     */
    public function setHydrator($resourceName, HydratorInterface $hydrator)
    {
        $resourceName = $this->canonicalizeEntityResourceName($resourceName);
        $this->hydrators[$resourceName] = $hydrator;

        return $this;
    }

    /**
     * Default hydrator class
     *
     * @return string
     */
    protected function getDefaultHydratorClass()
    {
        return 'rampage.orm.hydrator.ProxyHydrator';
    }

    /**
     * Fetch the hydrator for the given entity
     *
     * @param string $resourceName
     */
    public function getHydrator($resourceName)
    {
        $resourceName = $this->canonicalizeEntityResourceName($resourceName);

        if (isset($this->hydrators[$resourceName])) {
            return $this->hydrators[$resourceName];
        }

        $class = $this->getConfig()->getHydratorClass($this, $resourceName);
        if (!$class) {
            $class = $this->getDefaultHydratorClass();
        }

        $instance = $this->getObjectManager()->newInstance($class);

        $this->getConfig()->configureHydrator($instance, $this, $resourceName);
        $this->setHydrator($resourceName, $instance);

        return $instance;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\PlatformInterface::getConstraintMapper()
     */
    public function getConstraintMapper($constraint)
    {
        return null;
    }

    /**
     * Set platform capabilities
     *
     * @param PlatformCapabilities $capabilities
     */
    protected function setCapabilities(PlatformCapabilities $capabilities)
    {
        $this->capabilities = $capabilities;
        return $this;
    }

    /**
     * Initialize capabilities
     *
     * @return \rampage\orm\db\platform\PlatformCapabilities
     */
    protected function createCapabilities()
    {
        return new PlatformCapabilities(array(
            PlatformCapabilities::AUTO_IDENTITY
        ));
    }

    /**
     * Returns capabilities
     *
     * @return \rampage\orm\db\platform\PlatformCapabilities
     */
    public function getCapabilities()
    {
        if ($this->capabilities) {
            return $this->capabilities;
        }

        $capabilities = $this->createCapabilities();
        $this->setCapabilities($capabilities);

        return $capabilities;
    }

    /**
     * @return \rampage\orm\db\platform\DDLRendererInterface
     */
    protected function createDDLRenderer()
    {
        return new DDLRenderer($this);
    }

    /**
     * @see \rampage\orm\db\platform\PlatformInterface::getDDLRenderer()
     */
    public function getDDLRenderer()
    {
        if ($this->ddlRenderer) {
            return $this->ddlRenderer;
        }

        $renderer = $this->createDDLRenderer();
        if (!$renderer instanceof DDLRendererInterface) {
            throw new RuntimeException(sprintf(
                'Invalid DDL renderer: Should implement rampage\orm\platform\DDLRendererInterface, %s given.',
                is_object($renderer)? get_class($renderer) : gettype($renderer)
            ));
        }

        $this->ddlRenderer = $renderer;
        return $renderer;
    }
}
