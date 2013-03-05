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

/**
 * Abstract table definition
 */
abstract class AbstractTableDefinition extends NamedDefintion implements DefinitionInterface
{
    /**
     * Columns
     *
     * @var array
     */
    private $columns = array();

    /**
     * Primary key definition
     *
     * @var array
     */
    private $primaryKey = array();

    /**
     * Table indexes
     *
     * @var array
     */
    private $indexes = array();

    /**
     * Table references
     *
     * @var array
     */
    private $references = array();

    /**
     * Construction
     *
     * @param string $name The entity name
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

	/**
     * Add a column
     *
     * @param ColumnDefinition $column
     * @return \rampage\orm\db\ddl\AbstractTableDefinition
     */
    public function addColumn(ColumnDefinition $column)
    {
        $this->columns[$column->getName()] = $column;
        return $this;
    }

    /**
     * Add an index
     *
     * @param IndexDefinition $index
     */
    public function addIndex(IndexDefinition $index)
    {
        $this->indexes[$index->getName()] = $index;
        return $this;
    }

    /**
     * Add a reference defintion
     *
     * @param ReferenceDefinition $reference
     * @return \rampage\orm\db\ddl\AbstractTableDefinition
     */
    public function addReference(ReferenceDefinition $reference)
    {
        $this->references[$reference->getName()] = $reference;
        return $this;
    }

    /**
     * Returns a column definition
     *
     * @param string $name
     * @param string $type
     * @param int|array $sizeOrValues
     */
    public function column($name, $type = null, $sizeOrValues = null)
    {
        return new ColumnDefinition($name, $type, $sizeOrValues);
    }

    /**
     * Create a new reference defintion instance
     *
     * @param string $name
     * @param array $fields
     * @param string $table
     * @param array $referencedFields
     */
    public function reference($name, array $fields, $table, array $referencedFields)
    {
        new ReferenceDefinition($name, $fields, $table, $referencedFields);
    }

    /**
     * Returns the column definitions
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Primary key defintion
     *
     * @param array $primaryKey
     */
    public function setPrimaryKey($primaryKey)
    {
        if (!is_array($primaryKey)) {
            $primaryKey = array($primaryKey);
        }

        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * Returns the primary key
     *
     * @return array
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Returns the index definitions
     *
     * @return array
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * Reference defintions
     *
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }
}