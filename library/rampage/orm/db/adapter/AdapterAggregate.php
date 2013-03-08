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

use rampage\orm\db\platform\ServiceLocator as PlatformLocator;
use rampage\orm\db\platform\PlatformInterface;
use rampage\orm\db\metadata\Metadata;

use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\Feature\DriverFeatureInterface;
use rampage\db\driver\feature\DummyTransactionFeature;
use rampage\db\driver\feature\TransactionFeatureInterface;

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
     * Metadata
     *
     * @var \Zend\Db\Metadata\Metadata
     */
    protected $metadata = null;

    /**
     * Transaction feature
     *
     * @var \rampage\db\driver\feature\TransactionFeatureInterface
     */
    private $transactionFeature = null;

    /**
     * construct
     *
     * @param AdapterManager $adapterManager
     * @param PlatformLocator $platformLocator
     */
    public function __construct(AdapterManager $adapterManager, PlatformLocator $platformLocator, $adapterName = null)
    {
        $this->adapterManager = $adapterManager;
        $this->platformLocator = $platformLocator;

        if ($adapterName) {
            $this->setAdapterName($adapterName);
        }
    }

    /**
     * Adapter name
     *
     * @param string $name
     */
    public function setAdapterName($name)
    {
        $this->adapterName = (string)$name;
        return $this;
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
    public function getAdapter()
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
    public function getPlatform()
    {
        if ($this->platform) {
            return $this->platform;
        }

        $adapterPlatform = $this->getAdapter()->getPlatform();
        $platform = $this->getPlatformLocator()->get($adapterPlatform->getName(), array(
            'adapterPlatform' => $adapterPlatform
        ));

        $this->setPlatform($platform);
        return $platform;
    }

    /**
     * Set the adapter
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Set the platform instance
     *
     * @param \rampage\orm\db\platform\PlatformInterface $platform
     */
    public function setPlatform(PlatformInterface $platform)
    {
        $this->platform = $platform;
        return $this;
    }

    /**
     * Returns the transaction feature
     *
     * @return \rampage\db\driver\feature\TransactionFeatureInterface
     */
    public function getTransactionFeature()
    {
        if ($this->transactionFeature) {
            return $this->transactionFeature;
        }

        $driver = $this->getAdapter()->getDriver();
        $feature = ($driver instanceof DriverFeatureInterface)? $driver->getFeature(TransactionFeatureInterface::FEATURE_NAME) : null;

        if (!$feature instanceof TransactionFeatureInterface) {
            $feature = new DummyTransactionFeature();
        }

        $this->transactionFeature = $feature;
        return $feature;
    }

    /**
     * Get the sql builder instance
     *
     * @param string $table Optional
     */
    public function sql($table = null)
    {
        if ($table) {
            return new Sql($this->getAdapter(), $table);
        }

        if ($this->sql) {
            return $this->sql;
        }

        $sql = new Sql($this->getAdapter());
        $this->sql = $sql;

        return $sql;
    }

    /**
     * Metadata
     *
     * @return \rampage\orm\db\metadata\Metadata
     */
    public function metadata()
    {
        if (!$this->metadata) {
            $this->metadata = new Metadata($this->getAdapter());
        }

        return $this->metadata;
    }
}
