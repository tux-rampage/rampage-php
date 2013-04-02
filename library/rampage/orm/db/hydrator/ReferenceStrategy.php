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

use rampage\orm\entity\EntityInterface;
use rampage\orm\entity\type\EntityType;
use rampage\orm\repository\PersistenceFeatureInterface;
use rampage\orm\exception\RuntimeException;
use rampage\core\Utils;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Lazy model reference hydration strategy
 */
class ReferenceStrategy implements StrategyInterface
{
    /**
     * @var PersistenceFeatureInterface
     */
    private $repository = null;

    /**
     * @var EntityType
     */
    private $entityType = null;

    /**
     * @var string
     */
    private $ensureType = null;

    /**
     * @param PersistenceFeatureInterface $repository
     * @param EntityType $entityType
     * @param string $ensureType
     */
    public function __construct(PersistenceFeatureInterface $repository, EntityType $entityType, $ensureType = null)
    {
        $this->repository = $repository;
        $this->entityType = $entityType;
        $this->ensureType = $ensureType;
    }

    /**
     * @see \Zend\Stdlib\Hydrator\Strategy\StrategyInterface::hydrate()
     */
    public function hydrate($value)
    {
        if (!$value) {
            return null;
        }

        $instance = $this->repository->load($value, $this->entityType);
        if ($instance === false) {
            return null;
        }

        if ($this->ensureType && !($instance instanceof $this->ensureType)) {
            throw new RuntimeException(sprintf(
                'Invalid entity instance: %s expected, %s given',
                Utils::getPrintableClassName($this->ensureType),
                Utils::getPritableTypeName($instance)
            ));
        }

        return $instance;
    }

    /**
     * @see \Zend\Stdlib\Hydrator\Strategy\StrategyInterface::extract()
     */
    public function extract($value)
    {
        if ($value instanceof EntityInterface) {
            return $value->getId();
        }

        return $value;
    }

}