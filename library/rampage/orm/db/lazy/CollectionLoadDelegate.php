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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\db\lazy;

use rampage\orm\repository\PersistenceFeatureInterface;
use rampage\orm\query\QueryInterface;
use rampage\orm\entity\lazy\delegate\CollectionLoaderInterface;
use rampage\orm\entity\CollectionInterface;

/**
 * Collection load delegate
 */
class CollectionLoadDelegate implements CollectionLoaderInterface
{
    /**
     * Persistence query
     *
     * @var QueryInterface
     */
    protected $query = null;

    /**
     * Repository
     *
     * @var PersistenceFeatureInterface
     */
    protected $repository = null;

    /**
     * collection item class
     *
     * @var string
     */
    protected $itemClass = null;

    /**
     * Construct
     *
     * @param PersistenceFeatureInterface $repository
     * @param QueryInterface $query
     */
    public function __construct(PersistenceFeatureInterface $repository, QueryInterface $query, $itemClass = null)
    {
        $this->repository = $repository;
        $this->query = $query;

        if ($itemClass) {
            $this->itemClass = $itemClass;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\entity\lazy\delegate\CollectionLoaderInterface::load()
     */
    public function load(CollectionInterface $collection)
    {
        $this->repository->loadCollection($collection, $this->query);
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\entity\lazy\delegate\CollectionLoaderInterface::loadSize()
     */
    public function loadSize(CollectionInterface $collection)
    {
        $this->repository->loadCollectionSize($collection, $this->query);
    }
}