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

namespace rampage\orm\db\adapter;

use rampage\core\service\AbstractObjectLocator;
use rampage\core\ObjectManagerInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Adapter;

/**
 * Database adapter manager
 */
class AdapterManager extends AbstractObjectLocator
{
    /**
     * Adapter config
     *
     * @var \rampage\orm\db\adapter\ConfigInterface
     */
    private $config = null;

    /**
     * Required service types
     *
     * @var string
     */
    protected $requiredInstanceType = 'Zend\Db\Adapter\Adapter';

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
    public function __construct(ObjectManagerInterface $objectManager, ConfigInterface $config)
    {
        parent::__construct($objectManager);
        $this->config = $config;
    }

    /**
     * Retruns the adapter configuration
     *
     * @return \rampage\orm\db\adapter\ConfigInterface
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
     * @return \rampage\orm\db\Adapter
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
            $options = $config->getAdapterOptions($name);
            $instance = $this->create('rampage.orm.db.Adapter', array('driver' => $options));
            $this->ensureValidInstance($instance, $name);

            if (isset($options['initsql']) && is_array($options['initsql'])) {
                foreach ($options['initsql'] as $sql) {
                    $instance->query($sql, Adapter::QUERY_MODE_EXECUTE);
                }
            }

            $this->instances[$name] = $instance;
        }

        $this->setAdapter($cName, $instance);
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