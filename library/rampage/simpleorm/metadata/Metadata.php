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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm\metadata;

use rampage\db\Adapter;
use rampage\simpleorm\EntityManager;
use rampage\simpleorm\exception;

/**
 * Metadata container
 */
class Metadata
{
    /**
     * @var \rampage\db\Adapter
     */
    private $adapter = null;

    /**
     * @var DefinitionInterface
     */
    private $driver = null;

    /**
     * @var EntityManager
     */
    private $entityManager = null;

    /**
     * @var Entity[]
     */
    private $entities = array();

    /**
     * @param Adapter $adapter
     */
    public function __construct(EntityManager $entityManager, Adapter $adapter = null, DriverInterface $driver = null)
    {
        $this->entityManager = $entityManager;
        $this->adapter = $adapter? : $entityManager->getAdapter();

        if ($driver) {
            $this->driver = $driver;
        }
    }

    /**
     * @return \rampage\simpleorm\metadata\DefinitionInterface
     */
    protected function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return \rampage\simpleorm\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasEntity($name)
    {
        return (isset($this->entities[$name]) || $this->getDriver()->hasEntityDefintion($name));
    }

    /**
     * @param Entity $entity
     * @return self
     */
    public function addEntity(Entity $entity)
    {
        $this->entities[$entity->getName()] = $entity;
        return $this;
    }

    /**
     * @param string $name
     * @return \rampage\simpleorm\metadata\Entity
     * @throws \rampage\simpleorm\exception\EntityNotFoundException
     */
    public function getEntity($name)
    {
        if (!isset($this->entities[$name])) {
            if (!$this->getDriver()->hasEntityDefintion($name)) {
                throw new exception\EntityNotFoundException('Could not find entity: ' . $name);
            }

            $this->getDriver()->loadEntityDefintion($name, $this);
        }

        return $this->entities[$name];
    }
}
