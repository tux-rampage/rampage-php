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

namespace rampage\orm;

use Zend\ServiceManager\ServiceLocatorInterface;
use rampage\core\ObjectManagerInterface;

/**
 * Repository factory
 */
class RepositoryManager implements ServiceLocatorInterface
{
    /**
     * Object manager
     *
     * @var \rampage\core\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * ORM config
     *
     * @var \rampage\orm\ConfigInterface
     */
    private $config = null;

    /**
     * Initialized repositories
     *
     * @var array
     */
    private $repositories = array();

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager, ConfigInterface $config)
    {
        $this->objectManager = $objectManager;
        $this->setConfig($config);
    }

    /**
     * Returns the config instance
     *
     * @return \rampage\orm\ConfigInterface
     */
    protected function getConfig()
    {
        if (!$this->config) {
            throw new exception\DependencyException('Missing config instance');
        }

        return $this->config;
    }

    /**
     * Returns all configured repo names
     *
     * @return string[]
     */
    public function getRepositoryNames()
    {
        return $this->getConfig()->getRepositoryNames();
    }

    /**
     * Set configuration
     *
     * @param \rampage\orm\ConfigInterface $config
     * @return \rampage\orm\RepositoryManager
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Object manager
     *
     * @return \rampage\core\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Set a repository instance
     *
     * @param string $name
     * @param object $instance
     * @return \rampage\orm\RepositoryManager
     */
    public function setRepositoryInstance($name, RepositoryInterface $instance)
    {
        $this->repositories[$name] = $instance;
        return $this;
    }

    /**
     * Repository factory
     *
     * @param string $name
     * @return \rampage\orm\RepositoryInterface
     */
    public function get($name)
    {
        if (isset($this->repositories[$name])) {
            return $this->repositories[$name];
        }

        $class = $this->getConfig()->getRepositoryClass($name);
        $instance = $this->getObjectManager()->get($class, array(
            'config' => $this->getConfig(),
            'name' => $name
        ));

        $this->setRepositoryInstance($name, $instance);

        return $instance;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceLocatorInterface::has()
     */
    public function has($name)
    {
        $result = (isset($this->repositories[$name]) || $this->getConfig()->hasRepositoryConfig($name));
        return $result;
    }
}