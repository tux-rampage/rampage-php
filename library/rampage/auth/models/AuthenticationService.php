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
 * @package   rampage.auth
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\auth\models;

use Zend\Authentication\AuthenticationService as DefaultAuthenticationService;
use Zend\Authentication\Adapter\AdapterInterface as AuthAdapterInterface;
use rampage\auth\models\user\RepositoryInterface;

/**
 * Authentication service
 */
class AuthenticationService extends DefaultAuthenticationService
{
    /**
     * User repository
     *
     * @var \rampage\auth\models\user\RepositoryInterface
     */
    private $userRepository = null;

    /**
     * User repo
     *
     * @param RepositoryInterface $repository
     * @return \rampage\auth\models\AuthenticationService
     */
    public function setUserRepository(RepositoryInterface $repository)
    {
        $this->userRepository = $repository;
        return $this;
    }

    /**
     * User repository
     * @return \rampage\auth\models\user\RepositoryInterface
     */
    protected function getUserRepository()
    {
        return $this->userRepository;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Authentication\AuthenticationService::authenticate()
     */
    public function authenticate(AuthAdapterInterface $adapter = null)
    {
        $result = parent::authenticate();
        $user = $this->getUserRepository()->fetchUserFromAuthResult($result);
    }



}