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
use rampage\orm\query\QueryInterface;
use rampage\orm\entity\CollectionInterface;

/**
 * Presistence feature
 */
interface PersistenceFeatureInterface
{
    /**
     * Load the given entity
     *
     * When $entity is an instance of EntityInterface it should load
     * the data into this object
     *
     * @param string $id
     * @param string|\rampage\orm\entity\EntityInterface $entity
     * @return \rampage\orm\repository\PersistenceFeatureInterface $this Fluent interface
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
    public function getCollection(QueryInterface $query);

    /**
     * Load a collection
     *
     * @param Collection $collection
     * @param Query $query
     */
    public function loadCollection(CollectionInterface $collection, QueryInterface $query);
}