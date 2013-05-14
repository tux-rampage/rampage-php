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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm;

use Zend\Db\Sql\Sql;

/**
 * Strategy for auto incremented identifiers (MySQL auto_increment or MSSQL identity)
 */
abstract class AbstractIdentifierStrategy implements IdentifierStrategyInterface
{
    /**
     * @var \rampage\db\Adapter
     */
    private $adapter = null;

    /**
     * @var Sql
     */
    private $sql = null;

    /**
     * @var string
     */
    protected $table = null;

    /**
     * @return \rampage\db\Adapter
     */
    protected function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return \Zend\Db\Sql\Sql
     */
    protected function getSqlInstance()
    {
        if (!$this->sql) {
            $this->sql = new Sql($this->adapter);
        }

        return $this->sql;
    }

    /**
     * @see \rampage\simpleorm\IdentifierStrategyInterface::setEntityManager()
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->adapter = $entityManager->getAdapter();
        return $this;
    }

    /**
     * @see \rampage\simpleorm\IdentifierStrategyInterface::setTable()
     */
    public function setTable($table)
    {
        $table = trim($table);
        if (!$table) {
            throw new exception\InvalidArgumentException('Tablename must not be empty');
        }

        $this->table = $table;
    }
}
