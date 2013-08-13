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

namespace rampage\simpleorm;

use Zend\Db\Adapter\Adapter;

/**
 * DB Transaction gateway
 */
class DatabaseTransaction
{
    /**
     * @var \Zend\Db\Adapter\Driver\ConnectionInterface
     */
    private $connection = null;

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->connection = $adapter->getDriver()->getConnection();
    }

    /**
     * Ensure rollback when destructing this object
     */
    public function __destruct()
    {
        try {
            $this->rollback();
        } catch (\Exception $e) {
        }
    }

    /**
     * Start transaction
     *
     * @return self
     */
    public function start()
    {
        if ($this->active) {
            return $this;
        }

        $this->connection->beginTransaction();
        $this->active = true;

        return $this;
    }

    /**
     * Commit this transaction
     *
     * @return self
     */
    public function commit()
    {
        if (!$this->active) {
            return $this;
        }

        $this->connection->commit();
        $this->active = false;

        return $this;
    }

    /**
     * @return self
     */
    public function rollback()
    {
        if (!$this->active) {
            return $this;
        }

        $this->connection->rollback();
        $this->active = false;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }
}
