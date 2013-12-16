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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\auth\storage;

use rampage\auth\UserRepositoryAwareInterface;
use rampage\auth\UserRepositoryInterface;
use rampage\auth\IdentityInterface;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Session\ManagerInterface as SessionManagerInterface;

/**
 * Load identity from repo on demand
 *
 * This auth storage will load the identity from the current repository on demand.
 * It also ensures that the only the identifier is stored in the session instead of the whole model
 */
class RepositoryAwareStorage extends SessionStorage implements UserRepositoryAwareInterface
{
    /**
     * @var UserRepositoryInterface
     */
    protected $repository = null;

    /**
     * @var IdentityInterface
     */
    private $current = null;

    /**
     * {@inheritdoc}
     * @see \Zend\Authentication\Storage\Session::__construct()
     */
    public function __construct($namespace = null, $member = null, UserRepositoryInterface $repository = null, SessionManagerInterface $manager = null)
    {
        $this->repository = $repository;
        parent::__construct($namespace, $member, $manager);
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
     * {@inheritdoc}
     * @see \Zend\Authentication\Storage\Session::clear()
     */
    public function clear()
    {
        $this->current = null;
        parent::clear();

        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Authentication\Storage\Session::isEmpty()
     */
    public function isEmpty()
    {
        if (parent::isEmpty()) {
            return true;
        }

        $current = $this->read();
        return ($current === null);
    }

	/**
     * {@inheritdoc}
     * @see \Zend\Authentication\Storage\Session::read()
     */
    public function read()
    {
        if ($this->current !== null) {
            return $this->current;
        }

        $id = parent::read();
        if (!$id || !$this->repository) {
            return null;
        }

        $this->current = $this->repository->findOneByIdentity($id);
        return $this->current;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Authentication\Storage\Session::write()
     */
    public function write($contents)
    {
        if (!$this->repository || !($contents instanceof IdentityInterface)) {
            return $this;
        }

        $this->current = $contents;
        parent::write($contents->getIdentity());

        return $this;
    }
}
