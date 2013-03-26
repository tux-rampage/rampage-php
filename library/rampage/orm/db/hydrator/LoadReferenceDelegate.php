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

namespace rampage\orm\db\hydrator;

use rampage\orm\hydrator\LazyValueInterface;
use rampage\orm\repository\PersistenceFeatureInterface;
use rampage\orm\exception\RuntimeException;
use rampage\orm\entity\type\EntityType;

/**
 * Load reference delegate
 */
class LoadReferenceDelegate implements LazyValueInterface
{
    /**
     * Entity type
     *
     * @var EntityType
     */
    private $entityType = null;

    /**
     * Repository
     *
     * @var PersistenceFeatureInterface
     */
    private $repository = null;

    /**
     * @var string
     */
    private $ensureType = null;

    /**
     * @var mixed
     */
    private $id = null;

    /**
     * Construct
     *
     * @param PersistenceFeatureInterface $repository
     * @param EntityType $entityType
     * @param mixed $id
     * @param string $ensureType
     */
    public function __construct(PersistenceFeatureInterface $repository, EntityType $entityType, $id, $ensureType = null)
    {
        $this->repository = $repository;
        $this->entityType = $entityType;
        $this->id = $id;

        if ($ensureType) {
            $this->ensureType = strtr($ensureType, '.', '\\');
        }
    }

    /**
     * @see \rampage\orm\hydrator\LazyValueInterface::__invoke()
     */
    public function __invoke($attribute)
    {
        if ($this->id === null) {
            return null;
        }

        $instance = $this->repository->load($this->id, $this->entityType);
        if ($instance === false) {
            return null;
        }

        if ($this->ensureType && !($instance instanceof $this->ensureType)) {
            throw new RuntimeException(sprintf(
                'The loaded entity must be an instance of %s, %s given',
                strtr($this->ensureType, '\\', '.'),
                is_object($instance)? strtr(get_class($instance), '\\', '.') : gettype($instance)
            ));
        }

        return $instance;
    }
}