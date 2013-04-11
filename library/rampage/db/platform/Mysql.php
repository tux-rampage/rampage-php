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

namespace rampage\db\platform;

use Zend\Db\Adapter\Platform\Mysql as DefaultMysqlPlatform;
use Zend\Db\Adapter\Driver\DriverInterface;

/**
 * Mysql platform implementation
 */
class Mysql extends DefaultMysqlPlatform implements DriverAwareInterface
{
    /**
     * Driver instance
     *
     * @var DriverInterface
     */
    private $driver;

    /**
     * (non-PHPdoc)
     * @see \rampage\db\platform\DriverAwareInterface::setDriver()
     */
    public function setDriver($driver = null)
    {
        if ($driver instanceof DriverInterface) {
            $this->driver = $driver;
        }
    }

    /**
     * Driver resource
     *
     * @return mixed
     */
    protected function getDriverResource()
    {
        if (!$this->driver) {
            return null;
        }

        return $this->driver->getConnection()->getResource();
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Db\Adapter\Platform\Mysql::quoteValue()
     */
    public function quoteValue($value)
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            $format = (is_float($value))? '%0.0f' : '%d';
            return sprintf($format, $value);
        }

        // Format date time value
        if ($value instanceof \DateTime) {
            $value = $value->format('Y-m-d H:i:s');
        }

        $resource = $this->getDriverResource();

        if ($resource instanceof \PDO) {
            return $resource->quote($value);
        }

        if ($resource instanceof \mysqli) {
            return $resource->escape_string($value);
        }

        return mysql_escape_string($value);
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\Db\Adapter\Platform\Mysql::quoteValueList()
     */
    public function quoteValueList($valueList)
    {
        if (!is_array($valueList)) {
            $valueList = array($valueList);
        }

        $quoted = implode(', ', array_map(array($this, 'quoteValue'), $valueList));
        return $quoted;
    }
}
