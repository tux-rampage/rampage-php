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
 * @package   rampage.db
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\db\driver\pdo\feature;

use rampage\db\driver\feature\TransactionFeatureInterface;
use rampage\db\driver\pdo\Driver;
use Zend\Db\Adapter\Driver\Feature\AbstractFeature;

/**
 * Transaction feature
 */
class TransactionFeature extends AbstractFeature implements TransactionFeatureInterface
{
    /**
     * Transaction level to support multiple calls
     *
     * @var int
     */
    protected $level = 0;

    /**
     * Construct
     *
     * @param Driver $driver
     */
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Get the driver instance
     *
     * @return \rampage\db\driver\pdo\Driver The driver instance
     */
    protected function getDriver()
    {
        return $this->driver;
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\Db\Adapter\Driver\Feature\AbstractFeature::getName()
     */
    public function getName()
    {
        return TransactionFeatureInterface::FEATURE_NAME;
    }
	/**
     * (non-PHPdoc)
     * @see \rampage\db\driver\feature\TransactionFeatureInterface::commit()
     */
    public function commit()
    {
        $this->level--;

        if ($this->level < 0) {
            $this->level = 0;
        } else if ($this->level == 0) {
            $this->getDriver()->getConnection()->commit();
        }

        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\db\driver\feature\TransactionFeatureInterface::rollback()
     */
    public function rollback()
    {
        $this->level--;

        if ($this->level < 0) {
            $this->level = 0;
        } else if ($this->level == 0) {
            $this->getDriver()->getConnection()->rollback();
        }

        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\db\driver\feature\TransactionFeatureInterface::start()
     */
    public function start()
    {
        $this->level++;

        if ($this->level > 1) {
            return $this;
        }

        $this->getDriver()->getConnection()->beginTransaction();
        return $this;
    }
}
