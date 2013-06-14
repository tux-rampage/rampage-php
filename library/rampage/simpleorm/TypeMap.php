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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm;

/**
 * Type Mapping
 */
class TypeMap
{
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOL = 'bool';
    const TYPE_DATETIME = 'datetime';

    /**
     * @var array
     */
    protected $dbMap = array(
        'CHAR' => self::TYPE_STRING,
        'VARCHAR' => self::TYPE_STRING,
        'TINYTEXT' => self::TYPE_STRING,
        'MEDIUMTEXT' => self::TYPE_STRING,
        'LONGTEXT' => self::TYPE_STRING,
        'TEXT' => self::TYPE_STRING,
        'ENUM' => self::TYPE_STRING,

        'INT' => self::TYPE_INT,
        'INTEGER' => self::TYPE_INT,
        'TINYINT' => self::TYPE_INT,
        'SMALLINT' => self::TYPE_INT,
        'MEDIUMINT' => self::TYPE_INT,
        'BIGINT' => self::TYPE_INT,
        'DECIMAL' => self::TYPE_FLOAT,

        'BOOL' => self::TYPE_BOOL,
        'BOOLEAN' => self::TYPE_BOOL,

        'DATE' => self::TYPE_DATETIME,
        'TIME' => self::TYPE_DATETIME,
        'DATETIME' => self::TYPE_DATETIME,
        'TIMESTAMP' => self::TYPE_DATETIME,
    );

    /**
     * @param string $dbType
     * @param string $ormType
     */
    public function addMapping($dbType, $ormType)
    {
        $dbType = strtoupper($dbType);
        $this->dbMap[$dbType] = $ormType;

        return $this;
    }

    /**
     * Map database type to ORM type
     *
     * @param string $type
     */
    public function mapDbType($type)
    {
        $type = strtoupper($type);
        if (!isset($this->dbMap[$type])) {
            throw new exception\InvalidArgumentException('Unknown database type: ' . $type);
        }

        return $this->dbMap[$type];
    }
}