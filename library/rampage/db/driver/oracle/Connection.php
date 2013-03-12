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

namespace rampage\db\driver\oracle;

use Zend\Db\Adapter\Driver\Pdo\Connection as PdoConnection;

/**
 * PDO Connection
 */
class Connection extends PdoConnection
{
    /**
     * (non-PHPdoc)
     * @see \Zend\Db\Adapter\Driver\Pdo\Connection::getCurrentSchema()
     */
    public function getCurrentSchema()
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        /* @var $result \PDOStatement */
        $sql = "select sys_context('userenv', 'current_schema') from dual";
        $result = $this->resource->query($sql);
        if ($result instanceof \PDOStatement) {
            return $result->fetchColumn();
        }

        return false;
    }
}