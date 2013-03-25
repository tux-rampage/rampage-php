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
 * @package   rampage.auth
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\auth\service;

use Zend\ServiceManager\ServiceLocatorInterface;
use rampage\auth\models\config\RepositoryInterface as ConfigRepositoryInterface;
use rampage\auth\models\user\RepositoryManagerInterface;
use rampage\auth\models\config\InstanceConfigInterface;
use rampage\core\ObjectManagerInterface;

/**
 * Instance manager
 */
class AuthServiceManager implements ServiceLocatorInterface
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $adapterManager = null;

    /**
     * @var \rampage\auth\models\config\RepositoryInterface;
     */
    private $configRepository = null;

    /**
     * @var \rampage\auth\models\user\RepositoryManagerInterface
     */
    private $userRepositoryManager = null;

    /**
     * Object manager
     *
     * @var \rampage\core\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Instances
     *
     * @var array
     */
    private $instances = array();

    /**
     * Construct
     *
     * @param ServiceLocatorInterface $adapterManager
     * @param RepositoryInterface $configs
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        AuthAdapterManager $adapterManager,
        RepositoryManagerInterface $usersRepositories,
        ConfigRepositoryInterface $configs)
    {
        $this->adapterManager = $adapterManager;
        $this->configRepository = $configs;
    }

    /**
     * get service locator
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected function getAdapterManager()
    {
        return $this->adapterManager;
    }

    /**
     * Config repo
     *
     * @return \rampage\auth\models\config\RepositoryInterface
     */
    protected function getConfigRepository()
    {
        return $this->configRepository;
    }

    /**
     * User repository manager
     *
     * @return \rampage\auth\models\user\RepositoryManagerInterface
     */
    protected function getUserRepositoryManager()
    {
        return $this->userRepositoryManager;
    }

    /**
     * Object manager
     *
     * @return \rampage\core\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     *
     * @return object
     */
    protected function newServiceInstance()
    {
        return $this->getObjectManager()->get('rampage.auth.AuthService');
    }

    /**
     * Retrieve a registered instance
     *
     * @param  string  $name
     * @throws Exception\ServiceNotFoundException
     * @return \rampage\auth\models\AuthenticationService
     */
    public function get($name)
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        $aggregate = $this->getObjectManager()->get('rampage.auth.models.AdapterAggregation');
        $service = $this->getObjectManager()->get('rampage.auth.AuthService');
        $repository = $this->getUserRepositoryManager()->get($name);
        $configs = $this->getConfigRepository()->getInstanceConfigs($name);

        $service->setUserRepository($repository)
                ->setAdapter($aggregate);

        foreach ($configs as $config) {
            if (!$config instanceof InstanceConfigInterface) {
                continue;
            }

            $adapter = $this->getAdapterManager()->get($config->getAdapterType());
            if (!$repository->isAuthTypeSupported($adapter->getCode())) {
                continue;
            }

            $config->configure($adapter);
            $aggregate->addAdapter($adapter);
        }

        $this->instances[$name] = $service;
        return $service;
    }

    /**
     * Check for a registered instance
     *
     * @param  string|array  $name
     * @return bool
    */
    public function has($name)
    {
        return $this->getUserRepositoryManager()->has($name);
    }
}
