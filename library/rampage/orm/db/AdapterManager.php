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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\db;

use rampage\core\service\AbstractObjectLocator;
use rampage\core\ObjectManagerInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterInterface;

/**
 * Database adapter manager
 */
class AdapterManager extends AbstractObjectLocator
{
    /**
     * Adapter config
     *
     * @var \rampage\orm\db\AdapterConfigInterface
     */
    private $config = null;

    /**
     * Adapter instances
     *
     * @var array
     */
    protected $instances = array();

    /**
     * (non-PHPdoc)
     * @see \rampage\core\service\AbstractObjectLocator::__construct()
     */
    public function __construct(ObjectManagerInterface $objectManager, AdapterConfigInterface $config)
    {
        parent::__construct($objectManager);
        $this->config = $config;
    }

    /**
     * Retruns the adapter configuration
     *
     * @return \rampage\orm\db\AdapterConfigInterface
     */
    protected function getAdapterConfig()
    {
        return $this->config;
    }

    /**
     * Set an adapter instance
     *
     * @param string $name
     * @param AdapterInterface $adapter
     */
    public function setAdapter($name, AdapterInterface $adapter)
    {
        $name = $this->canonicalizeName($name);
        $this->instances[$name] = $adapter;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\service\AbstractObjectLocator::get()
     * @return \Zend\Db\Adapter\Adapter
     */
    public function get($name)
    {
        $cName = $this->canonicalizeName($name);
        if (isset($this->instances[$cName])) {
            return $this->instances[$cName];
        }

        $config = $this->getAdapterConfig();
        $name = $config->resolveAdapterAlias($cName);

        if (isset($this->instances[$name])) {
            $instance = $this->instances[$name];
        } else {
            $instance = $this->getObjectManager()->get('rampage.orm.db.Adapter', array('driver' => $config->getAdapterOptions($name)));
            $this->instances[$name] = $instance;
        }

        $this->instances[$cName] = $instance;
        return $instance;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\service\AbstractObjectLocator::has()
     */
    public function has($name)
    {
        return true;
    }
}