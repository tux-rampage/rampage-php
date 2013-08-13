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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Entity manager
 */
class EntityManager implements ServiceManagerAwareInterface
{
    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter = null;

    /**
     * @var UnitOfWorkInterface
     */
    protected $unitOfWork = null;

    /**
     * @var EntityDefinitionList
     */
    protected $definition = null;

    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager = null;

    /**
     * @var repositories
     */
    protected $repositories = array();

    /**
     * @param Adapter $adapter
     * @param string $definition
     * @param UnitOfWorkInterface $unitOfWork
     */
    public function __construct(Adapter $adapter, EntityDefinitionInterface $definition = null, UnitOfWorkInterface $unitOfWork = null)
    {
        if (!$definition instanceof EntityDefinitionList) {
            $definition = new EntityDefinitionList($definition);
        }

        $this->adapter = $adapter;
        $this->unitOfWork = $unitOfWork? : new UnitOfWork($this);
        $this->definition = $definition;
    }

    /**
     * @param string $name
     */
    protected function createRepository($name)
    {
        if (!$this->serviceManager) {
            return false;
        }

        return $this->serviceManager->get($name);
    }

    /**
     * @see \Zend\ServiceManager\ServiceManagerAwareInterface::setServiceManager()
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * @param string $entity
     * @param RepositoryInterface $repository
     * @return self
     */
    public function setRepository($entity, RepositoryInterface $repository)
    {
        $this->repositories[$entity] = $repository;
        return self;
    }

    /**
     * @param string $entity
     * @return \rampage\simpleorm\RepositoryInterface
     */
    public function getRepository($entity)
    {
        if (is_object($entity)) {
            $entity = get_class($entity);
        }

        $name = $this->definition->getRepositoryName($entity);

        if (isset($this->repositories[$name])) {
            return $this->repositories[$name];
        }

        $repository = $this->createRepository($name);
        $this->setRepository($name, $repository);

        return $repository;
    }

    /**
     * @return \rampage\simpleorm\UnitOfWorkInterface
     */
    public function getUnitOfWork()
    {
        return $this->unitOfWork;
    }

    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param object $object
     * @return self
     */
    public function persist($object)
    {
        if (!is_object($object)) {
            throw new exceptions\InvalidArgumentException(sprintf('%s expects an object as parameter, %s given', __METHOD__, gettype($object)));
        }

        if (!$repository = $this->getRepository($object)) {
            throw new exceptions\InvalidArgumentException(sprintf('Could not find a repository for "%s"', get_class($object)));
        }

        $this->unitOfWork->store($object, $repository);
        return $this;
    }

    /**
     * @param object $object
     * @return self
     */
    public function delete($object)
    {
        if (!is_object($object)) {
            throw new exceptions\InvalidArgumentException(sprintf('%s expects an object as parameter, %s given', __METHOD__, gettype($object)));
        }

        if (!$repository = $this->getRepository($object)) {
            throw new exceptions\InvalidArgumentException(sprintf('Could not find a repository for "%s"', get_class($object)));
        }

        $this->unitOfWork->delete($object);
        return $this;
    }

    /**
     * @return self
     */
    public function flush()
    {
        $this->unitOfWork->flush();
        return $this;
    }
}