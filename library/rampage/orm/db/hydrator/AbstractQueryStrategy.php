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

use rampage\orm\repository\PersistenceFeatureInterface;
use rampage\orm\entity\type\EntityType;
use rampage\orm\exception\InvalidArgumentException;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Abstract query strategy
 */
abstract class AbstractQueryStrategy implements StrategyInterface
{
    /**
     * Repository
     *
     * @var PersistenceFeatureInterface
     */
    private $repository = null;

    /**
     * Entity type
     *
     * @var EntityType
     */
    private $entityType = null;

    /**
     * Item class
     *
     * @var string
     */
    protected $itemClass = null;

    /**
     * Construct
     *
     * @param RepositoryInterface $repository
     */
    public function __construct(PersistenceFeatureInterface $repository, EntityType $entityType, $itemClass = null)
    {
        $this->repository = $repository;
        $this->entityType = $entityType;

        if ($itemClass) {
            $this->itemClass = $itemClass;
        }
    }

    /**
     * @return \rampage\orm\repository\PersistenceFeatureInterface
     */
    protected function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return \rampage\orm\entity\type\EntityType
     */
    protected function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Create query
     *
     * @param mixed $value
     * @throws InvalidArgumentException
     * @return \rampage\orm\db\hydrator\CollectionLoadDelegate
     */
    protected function createQuery($value)
    {
        $entityType = $this->getEntityType();
        $query = $this->getRepository()->query($entityType->getFullName());

        if (!is_array($value)) {
            $identifier = $entityType->getIdentifier();
            if (!$identifier->isMultiAttribute()) {
                throw new InvalidArgumentException('Invalid hydration value: array expected.');
            }

            $attribute = $identifier->getAttribute()->getName();
            $query->matches($query->equals($attribute, $value));
        } else {
            foreach ($value as $attribute => $attributeValue) {
                $query->matches($query->equals($attribute, $attributeValue));
            }
        }

        return $query;
    }
}