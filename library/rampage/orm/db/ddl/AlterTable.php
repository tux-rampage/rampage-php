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

namespace rampage\orm\db\ddl;

use rampage\orm\exception\InvalidArgumentException;
/**
 * Alter table definition
 */
class AlterTable extends AbstractTableDefinition
{
    /**
     * DDL Name
     */
    const DDL_NAME = 'ALTER TABLE';

    const DROP_TYPE_INDEX = 'index';
    const DROP_TYPE_REFERENCE = 'reference';
    const DROP_TYPE_PRIMARY = 'primary';

    /**
     * Drop definitions
     *
     * @var array
     */
    protected $drop = array(
        self::DROP_TYPE_REFERENCE => array(),
        self::DROP_TYPE_INDEX => array(),
        self::DROP_TYPE_PRIMARY => false,
    );

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\ddl\DefinitionInterface::getDdlDefintionName()
     */
    public function getDdlDefintionName()
    {
        return static::DDL_NAME;
    }

	/**
     * Add a drop element
     *
     * @param string $type
     * @param string $name
     */
    protected function drop($type, $name)
    {
        $type = strtolower($type);

        if (!array_key_exists($type, $this->drop)) {
            throw new InvalidArgumentException('Invalid drop type: ' . $type);
        }

        if ($type == self::DROP_TYPE_PRIMARY) {
            $this->drop[self::DROP_TYPE_PRIMARY] = true;
            return $this;
        }

        if (!$name) {
            throw new InvalidArgumentException('Missing name for drop ' . $type);
        }

        $this->drop[$type][$name] = $name;
        return $this;
    }

    /**
     * Drop a reference
     *
     * @param string $name
     */
    public function dropReference($name)
    {
        $this->drop(self::DROP_TYPE_REFERENCE, (string)$name);
        return $this;
    }

    /**
     * Drop the primary key
     *
     * @return \rampage\orm\db\ddl\AlterTable
     */
    public function dropPrimaryKey()
    {
        $this->drop(self::DROP_TYPE_PRIMARY, true);
        return $this;
    }

    /**
     * Drop an index
     *
     * @param string $name
     * @return \rampage\orm\db\ddl\AlterTable
     */
    public function dropIndex($name)
    {
        $this->drop(self::DROP_TYPE_INDEX, $name);
        return $this;
    }

    /**
     * Get drop elements
     *
     * @return array
     */
    public function getDropElements()
    {
        return $this->drop;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\ddl\AbstractTableDefinition::column()
     */
    public function column($name, $type = null, $sizeOrValues = null)
    {
        return new ChangeColumn($name, $type, $sizeOrValues);
    }

	/**
     * Drop a column
     *
     * @param string $name
     */
    public function dropColumn($name)
    {
        $this->addColumn($this->column($name)->setChangeType(ChangeColumn::CHANGETYPE_DROP));
    }

    /**
     * Change a column
     *
     * @param string $name
     * @param string $newName
     * @param string $type
     * @param int|array $sizeOrValues
     * @return \rampage\orm\db\ddl\ChangeColumn
     */
    public function modifyColumn($name, $type = null, $sizeOrValues = null, $newName = null)
    {
        $column = $this->column($name, $type, $sizeOrValues);

        $column->setChangeType(ChangeColumn::CHANGETYPE_MODIFY)
            ->setNewName($newName);

        return $column;
    }
}