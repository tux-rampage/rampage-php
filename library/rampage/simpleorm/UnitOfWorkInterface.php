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

namespace rampage\simpleorm;

/**
 * Persistence strategy interface
 */
interface UnitOfWorkInterface
{
    /**
     * This should mark the given object for persistence
     *
     * @param object $object
     */
    public function store($object, PersistenceGatewayInterface $persistenceGateway = null);

    /**
     * This should mark the object as deleted
     *
     * @param object $object
     */
    public function delete($object, PersistenceGatewayInterface $persistenceGateway = null);

    /**
     * This should commit all changes to the persistence layer
     *
     * @return self
     */
    public function flush();

    /**
     * This method should reset the persistence by flushing all pending object actions
     *
     * @return self
     */
    public function reset();

    /**
     * Allows to set the persistence state of an object
     *
     * @param object $object
     * @param ObjectPersistenceState $state
     */
    public function setObjectState($object, ObjectPersistenceState $state);

    /**
     * Returns the persistence state of an object
     *
     * @param object $object
     * @return boolean|\rampage\simpleorm\ObjectPersistenceState
     */
    public function getObjectState($object);
}