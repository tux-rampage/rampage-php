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

namespace rampage\orm\db\platform;

use rampage\orm\exception\RuntimeException;
use rampage\orm\db\ddl\DefinitionInterface;
use rampage\orm\db\ddl\CreateTable;
use rampage\orm\db\ddl\NamedDefintion;
use rampage\orm\db\ddl\ColumnDefinition;
use rampage\orm\db\ddl\AbstractTableDefinition;
use Zend\Text\Table\Column;

/**
 * DDL Renderer
 */
class DDLRenderer implements DdlRendererInterface
{
    /**
     * Adapter platform
     *
     * @var \rampage\orm\db\platform\PlatformInterface
     */
    private $platform = null;

    /**
     * Column type map
     *
     * @var array
     */
    protected $columnTypeMap = array(
        ColumnDefinition::TYPE_BLOB => 'BLOB',
        ColumnDefinition::TYPE_BOOL => 'INT',
        ColumnDefinition::TYPE_CLOB => 'TEXT',
        ColumnDefinition::TYPE_ENUM => 'ENUM',
        ColumnDefinition::TYPE_FLOAT => 'DECIMAL',
        ColumnDefinition::TYPE_INT => 'INT',
        ColumnDefinition::TYPE_TEXT => 'TEXT',
        ColumnDefinition::TYPE_VARCHAR => 'VARCHAR'
    );

    /**
     * Adapter platform
     *
     * @param AdapterPlatformInterface $platform
     */
    public function __construct(PlatformInterface $platform)
    {
        $this->platform = $platform;
    }

    /**
     * @return \rampage\orm\db\platform\PlatformInterface
     */
    protected function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Format identifier
     *
     * @param string $identifier
     * @return string
     */
    protected function formatIdentifier($identifier)
    {
        return strtolower($identifier);
    }

    /**
     * Quote Identifier
     *
     * @param unknown $identifier
     */
    protected function quoteIdentifier($identifier)
    {
        return $this->getPlatform()->getAdapterPlatform()->quoteIdentifier($identifier);
    }

    /**
     * Get table name
     *
     * @param unknown $entity
     */
    protected function getTable($entity)
    {
        return $this->getPlatform()->getTable($entity);
    }

    /**
     * Returns the field name for the given attribute
     *
     * @param string $entity
     * @param string $attribute
     * @return string
     */
    protected function getFieldName($entity, $attribute)
    {
        return $this->getPlatform()->getFieldMapper($entity)->mapAttribute($attribute);
    }

    /**
     * Render table name
     *
     * @param NamedDefintion $ddl
     * @return string
     */
    protected function renderTableName(NamedDefintion $ddl)
    {
        return $this->quoteIdentifier($this->getTable($ddl->getName()));
    }

    /**
     * Render field name
     *
     * @param string $entity
     * @param string|NamedDefintion $attribute
     * @return string
     */
    protected function renderFieldName($entity, $attribute)
    {
        if ($attribute instanceof NamedDefintion) {
            $attribute = $attribute->getName();
        }

        return $this->quoteIdentifier($this->getFieldName($entity, $attribute));
    }

    /**
     * Render column definition
     *
     * @param ColumnDefinition $column
     */
    protected function getColumnSpec(ColumnDefinition $column, AbstractTableDefinition $ddl)
    {
        switch ($column->getType()) {
            case ColumnDefinition::TYPE_VARCHAR:
                $size = ($column->getSize())?: 255;
                $type = 'VARCHAR(' . $column->getSize() . ')';
                break;

            case ColumnDefinition::TYPE_BLOB:
                $type = 'BLOB';
                break;

            case ColumnDefinition::TYPE_BOOL:
                $type = 'INT(1)';
                break;

            case ColumnDefinition::TYPE_CLOB:
                $type = 'TEXT';
                break;

            case ColumnDefinition::TYPE_ENUM:
                $type = 'ENUM(' . $this->getPlatform()->getAdapterPlatform()->quoteValueList($column->getValues()) . ')';
                break;

            case ColumnDefinition::TYPE_FLOAT:
                $type = 'DECIMAL(' . $column->getSize(true) . ', ' . $column->getPrecision(true) . ')';
                break;
        }

        $extra = '';
        $nullable = ($column->isNullable())? 'NULL' : 'NOT NULL';

        if (in_array($column->getName(), $ddl->getPrimaryKey())) {
            $nullable = 'NOT NULL';
        }

        return array($type, $extra, $nullable, $default);
    }

    /**
     * Render column defintion
     *
     * @param ColumnDefinition $column
     * @return string
     */
    protected function renderColumnDefintion(ColumnDefinition $column, AbstractTableDefinition $ddl)
    {
        $spec = $this->getColumnSpec($column);
        $spec = array_filter($spec);

        return implode(' ', $spec);
    }

    /**
     * Render create table ddl
     *
     * @param CreateTable $ddl
     * @return string
     */
    public function renderCreateTable(CreateTable $ddl)
    {
        $parts = array();

        foreach ($ddl->getColumns() as $column) {
            $parts[] = '';
        }

        $parts = implode("\n,", $parts);
        $sql = "
            CREATE TABLE {$this->renderTableName($ddl)} (
                $parts
            )
        ";

        return $sql;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\DDLRendererInterface::renderDdl()
     */
    public function renderDdl(DefinitionInterface $ddl)
    {
        switch ($ddl->getDdlDefintionName()) {
            case CreateTable::DDL_NAME:
                return $this->renderCreateTable($ddl);
        }

        throw new RuntimeException('Unsupported DDL statement: ' . $ddl->getDdlDefintionName());
    }


}