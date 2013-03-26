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

namespace rampage\orm\repository;

use rampage\orm\entity\EntityInterface;
use rampage\orm\entity\CollectionInterface;
use rampage\orm\query\QueryInterface;

/**
 * Presistence feature
 */
interface PersistenceFeatureInterface
{
    /**
     * Load the given entity
     *
     * When $entity is an instance of EntityInterface it should load
     * the data into this object.
     * Otherwise assume an $entity is an entity type name. The implementing
     * class should instanciate the entity and load the data into it.
     *
     * The loaded entity instance should be returned on success and false if
     * the entity does not exist
     *
     * @param string $id
     * @param string|\rampage\orm\entity\EntityInterface $entity
     * @return \rampage\orm\entity\EntityInterface|false
     */
    public function load($id, $entity);

    /**
     * Save the given entity
     *
     * @param EntityInterface $entity
     * @return \rampage\orm\repository\PersistenceFeatureInterface $this Fluent interface
     */
    public function save(EntityInterface $entity);

    /**
     * Delete the given entity
     *
     * @param EntityInterface $entity
     * @return \rampage\orm\repository\PersistenceFeatureInterface $this Fluent interface
     */
    public function delete(EntityInterface $entity);

    /**
     * Build a query instance for an entity
     *
     * @param string $entity
     * @return \rampage\orm\query\Query
     */
    public function query($entity = null);

    /**
     * Get a collection for the given query
     *
     * @param string $entity
     * @param Query $query
     */
    public function getCollection(QueryInterface $query, $itemClass = null);

    /**
     * Load a collection
     *
     * @param Collection $collection
     * @param Query $query
     * @param string $itemClass Instanciate items using this class
     */
    public function loadCollection(CollectionInterface $collection, QueryInterface $query, $itemClass = null);

    /**
     * Load the collection size
     *
     * @param CollectionInterface $collection
     * @param QueryInterface $query
     * @return \rampage\orm\db\AbstractRepository
     */
    public function loadCollectionSize(CollectionInterface $collection, QueryInterface $query);
}