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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm;

use rampage\orm\entity\type\EntityType;
use rampage\orm\entity\EntityInterface;
use rampage\orm\entity\type\ConfigInterface as EntityTypeConfigInterface;
use rampage\orm\exception\RuntimeException;

/**
 * Abstract repository
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * Repository name
     *
     * @var string
     */
    protected $name = null;

    /**
     * Entity types
     *
     * @var array
     */
    protected $entityTypes = array();

    /**
     * Database adapter name
     *
     * @var string
     */
    protected $adapterName = 'default';

    /**
     * Repository Config
     *
     * @var \rampage\orm\ConfigInterface
     */
    private $config = null;

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
     * @return \rampage\orm\ConfigInterface|\rampage\orm\entity\type\ConfigInterface
     */
    protected function getConfig()
    {
        return $this->config;
    }
}