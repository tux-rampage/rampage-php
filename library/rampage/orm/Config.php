<?php
/**
 * This is part of @application_name@
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
 * @package   @package_name@
 * @author    Axel Helmert
 * @copyright Copyright (c) 2012 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm;
use rampage\core\xml\Config as XmlConfig;
use rampage\core\ObjectManagerInterface;

/**
 * Config
 */
class Config extends XmlConfig implements ConfigInterface
{
    /**
     * Object manager instance
     *
     * @var \rampage\core\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::__construct()
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Object Manager instance
     *
     * @return \rampage\core\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

	/**
     * Returns the repository class name
     *
     * @param string $name
     * @return string|null
     */
    protected function getRepositoryClass($name)
    {
        $node = $this->getNode("repository[@name='$name']");
        if (!$node) {
            return null;
        }

        $class = isset($node['class'])? (string)$node['class'] : (string)$node['name'];
        return $class;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\ConfigInterface::hasRepositoryConfig()
     */
    public function hasRepositoryConfig($name)
    {
        $class = $this->getRepositoryClass($name);
        return !empty($class);
    }

    /**
     * Returns the adapter name for the given repository name
     *
     * @return string|null
     */
    protected function getRepositoryAdapterName($repositoryName)
    {
        $node = $this->getNode("repository[@name='$repositoryName']/adapter");
        if (!$node || !isset($node['service'])) {
            return null;
        }

        return trim((string)$node['service']);
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\ConfigInterface::configureRepository()
     */
    public function configureRepository(RepositoryInterface $repository)
    {
        $name = $this->getRepositoryAdapterName($repository->getName());
        if ($name) {
            $repository->setAdapterName($name);
        }

        return $this;
    }
}