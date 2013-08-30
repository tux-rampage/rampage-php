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

use SplQueue;

/**
 * Persistence strategy
 */
class UnitOfWork implements UnitOfWorkInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager = null;

    /**
     * @var ObjectQueue
     */
    private $pendingToStore = null;

    /**
     * @var ObjectQueue
     */
    private $pendingToDelete = null;

    /**
     * @var ResetableObjectStorage
     */
    private $objectStates = null;

    /**
     * Construct
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->pendingToStore = new ObjectQueue();
        $this->pendingToDelete = new ObjectQueue();
        $this->objectStates = new ResetableObjectStorage();
    }

    /**
     * @param object $object
     * @param ObjectPersistenceState $state
     */
    public function setObjectState($object, ObjectPersistenceState $state)
    {
        $this->objectStates[$object] = $state;
        return $this;
    }

    /**
     * Returns the object state
     *
     * @param object $object
     * @return boolean|\rampage\simpleorm\ObjectPersistenceState
     */
    public function getObjectState($object)
    {
        if (!$this->objectStates->contains($object)) {
            return false;
        }

        return $this->objectStates[$object];
    }

    /**
     * @see \rampage\simpleorm\PersistenceStrategyInterface::store()
     */
    public function store($object, PersistenceGatewayInterface $persistenceGateway = null)
    {
        if ($this->pendingToDelete->contains($object)) {
            $this->pendingToDelete->detach($object);
        }

        if (!$this->pendingToStore->contains($object) || ($persistenceGateway !== null)) {
            $this->pendingToStore->attach($object, $persistenceGateway);
        }

        return $this;
    }

    /**
     * @see \rampage\simpleorm\PersistenceStrategyInterface::delete()
     */
    public function delete($object, PersistenceGatewayInterface $persistenceGateway = null)
    {
        if ($this->pendingToStore->contains($object)) {
            $this->pendingToStore->detach($object);
        }

        if (!$this->pendingToDelete->contains($object) || ($persistenceGateway !== null)) {
            $this->pendingToDelete->attach($object, $persistenceGateway);
        }

        return $this;
    }

    /**
     * @param object $object
     * @return \rampage\simpleorm\PersistenceGatewayInterface
     */
    protected function getRepositoryByObject($object)
    {
        $class = get_class($object);
        return $this->entityManager->getRepository($class);
    }

    /**
     * @param ObjectQueue $queue
     * @param object $object
     * @return \rampage\simpleorm\PersistenceGatewayInterface
     */
    private function getPersistenceGateway(ObjectQueue $queue, $object)
    {
        $gateway = $queue->getInfo();
        if (!$gateway instanceof PersistenceGatewayInterface) {
            $gateway = $this->getRepositoryByObject($object);
        }

        return $gateway;
    }

    /**
     * @see \rampage\simpleorm\PersistenceStrategyInterface::flush()
     */
    public function flush()
    {
        $transaction = new TransactionAggregate();
        $transaction->start();

        try {
            foreach ($this->pendingToStore as $object) {
                $gw = $this->getPersistenceGateway($this->pendingToStore, $object);

                if ($gw instanceof TransactionProviderInterface) {
                    $transaction->addTransaction($gw->getTransaction());
                }

                $gw->store($object);
            }

            foreach ($this->pendingToDelete as $object) {
                $gw = $this->getPersistenceGateway($this->pendingToDelete, $object);

                if ($gw instanceof TransactionProviderInterface) {
                    $transaction->addTransaction($gw->getTransaction());
                }

                $gw->delete($object);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }

        $this->reset();
        return $this;
    }

    /**
     * @see \rampage\simpleorm\PersistenceStrategyInterface::reset()
     */
    public function reset()
    {
        $this->pendingToDelete->reset();
        $this->pendingToStore->reset();
        $this->objectStates->reset();

        return $this;
    }
}
