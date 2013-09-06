<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2013 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\auth;

use Zend\Authentication\Adapter\AbstractAdapter;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\ValidatableAdapterInterface;
use Zend\Authentication\Result as AuthResult;
use Zend\Stdlib\SplPriorityQueue;


/**
 * Aggregates multiple authentication adapters
 */
class AuthAdapterComposite extends AbstractAdapter implements UserRepositoryAwareInterface
{
    /**
     * @var SplPriorityQueue
     */
    protected $adapters = null;

    /**
     * @var UserRepositoryInterface
     */
    protected $repository = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->adapters = new SplPriorityQueue();
    }

    /**
     * Add an authentication adapter
     *
     * @param AdapterInterface $adapter
     * @param number $priority
     * @return self
     */
    public function addAdapter(AdapterInterface $adapter, $priority = 10)
    {
        $this->adapters->insert($adapter, $priority);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\auth\UserRepositoryAwareInterface::setUserRepository()
     */
    public function setUserRepository(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
        return $this;
    }

	/**
     * Inject dependencies into the given adapter
     *
     * @param AdapterInterface $adapter
     * @return self
     */
    protected function injectAdapterDependencies(AdapterInterface $adapter)
    {
        if ($adapter instanceof ValidatableAdapterInterface) {
            $adapter->setIdentity($this->getIdentity());
            $adapter->setCredential($this->getCredential());
        }

        if ($this->repository && ($adapter instanceof UserRepositoryAwareInterface)) {
            $adapter->setUserRepository($this->repository);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Authentication\Adapter\AdapterInterface::authenticate()
     */
    public function authenticate()
    {
        $queue = clone $this->adapters;

        foreach ($this->adapters as $adapter) {

            $result = $adapter->authenticate();
            if ($result->isValid()) {
                return $result;
            }
        }

        return new AuthResult(AuthResult::FAILURE);
    }
}
