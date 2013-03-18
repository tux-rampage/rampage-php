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

use rampage\orm\db\AbstractRepository;
use rampage\orm\db\platform\hydrator\FieldHydratorInterface;
use rampage\orm\db\platform\hydrator\MappingHydratorInterface;
use rampage\orm\db\platform\FieldMapper;

use rampage\orm\entity\type\EntityType;
use rampage\orm\ValueObjectInterface;
use rampage\orm\exception\InvalidArgumentException;

use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Repository hydrator
 */
class Repository implements HydratorInterface, FieldHydratorInterface
{
    /**
     * Platform Hydrator
     *
     * @var FieldHydratorInterface
     */
    private $platformHydrator = null;

    /**
     * Repository
     *
     * @var AbstractRepository
     */
    private $repository = null;

    /**
     * Field Mapper
     *
     * @var \rampage\orm\db\platform\FieldMapper
     */
    private $fieldMapper = null;

    /**
     * Entity type
     *
     * @var EntityType
     */
    private $entityType = null;

    /**
     * Construct
     *
     * @param AbstractRepository $repository
     * @param HydratorInterface $platformHydrator
     */
    public function __construct(AbstractRepository $repository, HydratorInterface $platformHydrator, FieldMapper $mapper, EntityType $entityType)
    {
        $this->repository = $repository;
        $this->platformHydrator = $platformHydrator;
        $this->fieldMapper = $mapper;
        $this->entityType = $entityType;
    }

    /**
     * Entity type definition
     *
     * @return \rampage\orm\entity\type\EntityType
     */
    protected function getEntityType()
    {
        return $this->entityType;
    }

	/**
     * Platform hydrator
     *
     * @return \rampage\orm\db\platform\hydrator\FieldHydratorInterface
     */
    protected function getPlatformHydrator()
    {
        return $this->platformHydrator;
    }

	/**
     * Repository
     *
     * @return \rampage\orm\db\AbstractRepository
     */
    protected function getRepository()
    {
        return $this->repository;
    }

    /**
     * Feld mapper
     *
     * @return \rampage\orm\db\platform\FieldMapper
     */
    protected function getFieldMapper()
    {
        return $this->fieldMapper;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\hydrator\FieldHydratorInterface::getAllowedFields()
     */
    public function getAllowedFields()
    {
        if (!$this->getPlatformHydrator() instanceof FieldHydratorInterface) {
            return null;
        }

        return $this->getPlatformHydrator()->getAllowedFields();
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\hydrator\FieldHydratorInterface::setAllowedFields()
     */
    public function setAllowedFields(array $fields = null)
    {
        if (!$this->getPlatformHydrator() instanceof FieldHydratorInterface) {
            return $this;
        }

        $this->getPlatformHydrator()->setAllowedFields($fields);
        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::extract()
     */
    public function extract($object)
    {
        return $this->getPlatformHydrator()->extract($object);
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        if (!$object instanceof ValueObjectInterface) {
            throw new InvalidArgumentException(sprintf(
                'Invalid entity specified. Instance must implement rampage.orm.ValueObjectInterface, "%s" given.',
                is_object($object)? strtr(get_class($object), '\\', '.') : gettype($object)
            ));
        }

        $parent = $this->getPlatformHydrator();
        $mapper = ($parent instanceof MappingHydratorInterface)? $parent->getFieldMapper() : $this->getFieldMapper();
        $parent->hydrate($data, $object);

        $id = $this->getRepository()->prepareIdForObject($this->getEntityType(), $data, $mapper);
        $object->setId($id);

        return $object;
    }
}