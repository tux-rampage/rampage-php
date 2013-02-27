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

namespace rampage\orm\db\adapter;

use Zend\Db\Sql\Sql;
use rampage\orm\db\platform\ServiceLocator as PlatformLocator;
use Zend\Db\Adapter\Adapter;

/**
 * Adapter
 */
class AdapterAggregate
{
    /**
     * Adapter manager
     *
     * @var AdapterManager
     */
    private $adapterManager = null;

    /**
     * Platform locator
     *
     * @var PlatformLocator
     */
    private $platformLocator = null;

    /**
     * Current DB adapter
     *
     * @var Adapter
     */
    private $adapter = null;

    /**
     * Current platform
     *
     * @var \rampage\orm\db\platform\PlatformInterface
     */
    private $platform = null;

    /**
     * SQL instance
     *
     * @var array
     */
    private $sql = null;

    /**
     * the adapter name
     *
     * @var string
     */
    protected $adapterName = null;

    /**
     * construct
     *
     * @param AdapterManager $adapterManager
     * @param PlatformLocator $platformLocator
     */
    public function __construct(AdapterManager $adapterManager, PlatformLocator $platformLocator)
    {
        $this->adapterManager = $adapterManager;
        $this->platformLocator = $platformLocator;
    }

    /**
     * returns the adapter manager
     *
     * @return \rampage\orm\db\adapter\AdapterManager
     */
    protected function getAdapterManager()
    {
        return $this->adapterManager;
    }

    /**
     * Returns the platform locator
     *
     * @return \rampage\orm\db\platform\ServiceLocator
     */
    protected function getPlatformLocator()
    {
        return $this->platformLocator;
    }

    /**
     * Returns the adapter to use
     *
     * @return \Zend\Db\Adapter\Adapter
     */
    protected function getAdapter()
    {
        if ($this->adapter) {
            return $this->adapter;
        }

        $adapter = $this->getAdapterManager()->get($this->adapterName);
        $this->setAdapter($adapter);

        return $adapter;
    }

    /**
     * Returns te orm db platform instance
     *
     * @return \rampage\orm\db\platform\PlatformInterface
     */
    protected function getPlatform()
    {
        if ($this->platform) {
            return $this->platform;
        }

        return $platform;
    }

	/**
     *
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     */
    protected function setAdapter($adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

	/**
     *
     *
     * @param \rampage\orm\db\platform\PlatformInterface $platform
     */
    protected function setPlatform($platform)
    {
        $this->platform = $platform;
        return $this;
    }



}