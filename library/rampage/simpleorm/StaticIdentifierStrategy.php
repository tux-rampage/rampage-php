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

use Zend\Db\Sql\Predicate\Predicate;

/**
 * Strategy for auto incremented identifiers (MySQL auto_increment or MSSQL identity)
 */
class StaticIdentifierStrategy extends AbstractIdentifierStrategy
{
    /**
     * @var array
     */
    private $fields = array();

    /**
     * @see \rampage\simpleorm\IdentifierStrategyInterface::exists()
     */
    public function exists($data)
    {
        if (!$this->table) {
            throw new exception\LogicException('No table name is specified');
        }

        $select = $this->getSqlInstance()->select($this->table);
        $select->where($this->getWherePredicate($data));

        $row = $this->getSqlInstance()
            ->prepareStatementForSqlObject($select)
            ->execute()
            ->current();

        return ($row)? true : false;
    }

    /**
     * @see \rampage\simpleorm\IdentifierStrategyInterface::getNewIdentifierValue()
     */
    public function getNewIdentifierValue()
    {
        return false;
    }

    /**
     * @see \rampage\simpleorm\IdentifierStrategyInterface::getWherePredicate()
     */
    public function getWherePredicate($data)
    {
        if (empty($this->fields)) {
            throw new exception\RuntimeException('No identifier field defined');
        }

        $predicate = new Predicate();
        foreach ($this->fields as $field) {
            $value = (isset($data[$field]))? $data[$field] : null;
            $predicate->equalTo($field, $value);
        }

        return $predicate;
    }

    /**
     * @see \rampage\simpleorm\IdentifierStrategyInterface::prepareInsert()
     */
    public function prepareInsert(&$data)
    {
        return $this;
    }

    /**
     * @see \rampage\simpleorm\IdentifierStrategyInterface::setFields()
     */
    public function setFields(array $fields)
    {
        if (count($fields) < 1) {
            throw new exception\InvalidArgumentException('A static identifier must at least have one field');
        }

        $this->fields = $fields;
        return $this;
    }
}
