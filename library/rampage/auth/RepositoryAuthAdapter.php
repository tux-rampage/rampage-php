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

namespace rampage\auth;

use Zend\Authentication\Adapter\AbstractAdapter;
use Zend\Authentication\Result;

class RepositoryAuthAdapter extends AbstractAdapter implements UserRepositoryAwareInterface
{
    /**
     * @var UserRepositoryInterface
     */
    protected $repository = null;

    /**
     * @var PasswordStrategyInterface
     */
    protected $passwordStrategy = null;

    /**
     * @param PasswordStrategyInterface $passwordStrategy
     */
    public function __construct(UserRepositoryInterface $repository, PasswordStrategyInterface $passwordStrategy = null)
    {
        $this->passwordStrategy = $passwordStrategy? : new PasswordStrategy();
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
     * @see \Zend\Authentication\Adapter\AdapterInterface::authenticate()
     */
    public function authenticate()
    {
        if (!$this->repository) {
            return new Result(Result::FAILURE);
        }

        $user = $this->repository->findOneByIdentity($this->getIdentity());
        if (!$user instanceof IdentityInterface) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND);
        }

        if (!$this->passwordStrategy->verify($this->getCredential(), $user->getCredentialHash())) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID);
        }

        return new Result(Result::SUCCESS, $user);
    }
}
