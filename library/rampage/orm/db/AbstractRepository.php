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

use rampage\orm\RepositoryInterface;
use rampage\orm\repository\PersistenceFeatureInterface;

/**
 * Abstract DB repository
 */
class AbstractRepository implements RepositoryInterface, PersistenceFeatureInterface
{
    private $queryMapper = null;

    /**
     * Repository name
     *
     * @var string
     */
    private $name = null;

    /**
     * Database adapter
     *
     * @var string
     */
    private $adapter = null;

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
        // TODO Auto-generated method stub

    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\RepositoryInterface::setConfig()
     */
    public function setConfig(\rampage\orm\ConfigInterface $config)
    {
        // TODO Auto-generated method stub

    }
	/**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::delete()
     */
    public function delete(\rampage\orm\entity\EntityInterface $entity)
    {
        // TODO Auto-generated method stub

    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::getCollection()
     */
    public function getCollection(\rampage\orm\query\QueryInterface $query)
    {
        // TODO Auto-generated method stub

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
     * (non-PHPdoc)
     * @see \rampage\orm\repository\PersistenceFeatureInterface::loadCollection()
     */
    public function loadCollection(\rampage\orm\entity\CollectionInterface $collection,\rampage\orm\query\QueryInterface $query)
    {
        // TODO Auto-generated method stub

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