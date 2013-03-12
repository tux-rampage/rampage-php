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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\db\platform\oracle;

use rampage\orm\db\platform\DDLRenderer as DefaultDDLRenderer;

use rampage\orm\db\ddl\AlterTable;
use rampage\orm\db\ddl\ChangeColumn;
use rampage\orm\db\ddl\ColumnDefinition;
use rampage\orm\db\ddl\CreateTable;
use rampage\orm\db\ddl\AbstractTableDefinition;
use rampage\orm\db\platform\SequenceSupportInterface;
use rampage\orm\exception\InvalidArgumentException;
use rampage\orm\db\ddl\ReferenceDefinition;
use rampage\orm\db\ddl\IndexDefinition;

/**
 * Oracle ddl renderer
 */
class DDLRenderer extends DefaultDDLRenderer
{
    /**
     * Column type map
     *
     * @var array
     */
    protected $columnTypeMap = array(
        ColumnDefinition::TYPE_BLOB => 'BLOB',
        ColumnDefinition::TYPE_BOOL => 'NUMBER',
        ColumnDefinition::TYPE_CLOB => 'CLOB',
        ColumnDefinition::TYPE_ENUM => 'ENUM',
        ColumnDefinition::TYPE_FLOAT => 'NUMBER',
        ColumnDefinition::TYPE_INT => 'NUMBER',
        ColumnDefinition::TYPE_TEXT => 'VARCHAR2(4000)',
        ColumnDefinition::TYPE_VARCHAR => 'VARCHAR2',
        ColumnDefinition::TYPE_DATE => 'DATE',
        ColumnDefinition::TYPE_DATETIME => 'DATE',
    );

    /**
     * Text types
     *
     * @var array
     */
    protected $textTypes = array(
        ColumnDefinition::TYPE_CLOB,
        ColumnDefinition::TYPE_TEXT,
        ColumnDefinition::TYPE_VARCHAR,
    );

    /**
     * Ensure max length of identifiers
     *
     * @param string $identifier
     * @param string $quoted
     * @throws InvalidArgumentException
     * @return \rampage\orm\db\platform\oracle\DDLRenderer
     */
    protected function ensureValidIdentifierLength($identifier, $quoted = false)
    {
        $length = strlen($identifier);

        if ($quoted) {
            $length -= 2;
        }

        if ($length > 30) {
            throw new InvalidArgumentException(sprintf(
                '[Oracle] Identifier %s is longer than 30 characters (%d characters).',
                $identifier,
                strlen($identifier)
            ));
        }

        return $this;
    }

    /**
     * @see \rampage\orm\db\platform\DDLRenderer::formatIdentifier()
     */
    protected function formatIdentifier($identifier)
    {
        $this->ensureValidIdentifierLength($identifier);
        return strtoupper($identifier);
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\DDLRenderer::renderFieldName()
     */
    protected function renderFieldName($entity, $attribute)
    {
        $identifier = parent::renderFieldName($entity, $attribute);
        $this->ensureValidIdentifierLength($identifier, true);

        return $identifier;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\DDLRenderer::renderTableName()
     */
    protected function renderTableName($ddlOrName)
    {
        $identifier = parent::renderTableName($ddlOrName);
        $this->ensureValidIdentifierLength($identifier, true);

        return $identifier;
    }

	/**
     * @see \rampage\orm\db\platform\DDLRenderer::renderAlterColumn()
     */
    protected function renderAlterColumn(AlterTable $ddl, ChangeColumn $column)
    {
        $colName = $this->renderFieldName($ddl, $column->getName());
        $sql = "MODIFY ($colName {$this->renderColumnDefintion($column, $ddl)})";

        if ($column->getNewName()) {
            $newColName = $this->renderFieldName($ddl, $column->getNewName());
            $sql .= ",\nRENAME COLUMN $colName TO $newColName";
        }

        return $sql;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\DDLRenderer::getColumnSpec()
     */
    protected function getColumnSpec(ColumnDefinition $column, AbstractTableDefinition $ddl)
    {
        $sequence = array('type', 'typeExtra', 'default', 'nullable', 'extra');
        $unordered = parent::getColumnSpec($column, $ddl);
        $spec = array();

        foreach ($sequence as $type) {
            $spec[$type] = (isset($unordered[$type]))? $unordered[$type] : '';
        }

        // NULL keyword for allowing null values must not be provided in CREATE TABLE statements
        if ($ddl->getDdlDefintionName() == CreateTable::DDL_NAME) {
            if ($spec['nullable'] == 'NULL') {
                $spec['nullable'] = '';
            }
        }

        return $spec;
    }

	/**
     * @see \rampage\orm\db\platform\DDLRenderer::renderAddColumn()
     */
    protected function renderAddColumn(AlterTable $ddl, ChangeColumn $column)
    {
        return "ADD ({$this->renderFieldName($ddl, $column)} {$this->renderColumnDefintion($column, $ddl)})";
    }

    /**
     * Render index fields
     *
     * @param IndexDefinition $index
     * @param AbstractTableDefinition $ddl
     */
    protected function renderIndexFields(IndexDefinition $index, AbstractTableDefinition $ddl)
    {
        $fields = array();

        foreach ($index->getFields() as $info) {
            @list($attribute, $order) = $info;
            $field = $this->renderFieldName($ddl, $attribute);
            $fields[] = $field;
        }

        if (empty($fields)) {
            return '';
        }

        $fields = implode(', ', $fields);
        return $fields;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\DDLRenderer::renderIndex()
     */
    protected function renderIndex(IndexDefinition $index, AbstractTableDefinition $ddl)
    {
        if (!$index->isUnique()) {
            return '';
        }

        return parent::renderIndex($index, $ddl);
    }

    /**
     * create index renderer
     *
     * @param IndexDefinition $index
     * @param AbstractTableDefinition $ddl
     * @return string
     */
    protected function renderCreateIndex(IndexDefinition $index, AbstractTableDefinition $ddl)
    {
        if ($index->isUnique()) {
            return '';
        }

        $fields = $this->renderIndexFields($index, $ddl);
        if (!$fields) {
            return '';
        }

        $sql = "CREATE INDEX {$this->renderKeyName($index->getName())} ON {$this->renderTableName($ddl)}($fields)";
        return $sql;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\DDLRenderer::renderCreateTable()
     */
    public function renderCreateTable(CreateTable $ddl)
    {
        $parts = array();

        foreach ($ddl->getColumns() as $column) {
            $parts[] = $this->renderFieldName($ddl->getName(), $column->getName())
                     . ' ' . $this->renderColumnDefintion($column, $ddl);
        }

        $parts[] = $this->renderPrimaryKey($ddl);

        foreach ($ddl->getIndexes() as $index) {
            $sql[] = $this->renderIndex($index, $ddl);
        }

        foreach ($ddl->getReferences() as $reference) {
            $parts[] = $this->renderForeignKey($reference, $ddl);
        }

        // Ensure there are no empty parts
        $parts = array_map('trim', $parts);
        $parts = array_filter($parts);

        $sql = array();
        $sql[] = "CREATE TABLE {$this->renderTableName($ddl)} (\n\t"
               . implode(",\n\t", $parts)
               . "\n) {$this->renderCreateTableOptions($ddl)}";


        foreach ($ddl->getIndexes() as $index) {
            $sql[] = $this->renderCreateIndex($index, $ddl);
        }

        $sql = array_filter($sql);
        $platform = $this->getPlatform();
        $primary = $ddl->getPrimaryKey();

        if (!$platform instanceof SequenceSupportInterface) {
            return $sql;
        }

        /* @var $column ColumnDefinition */
        foreach ($ddl->getColumns() as $column) {
            $isPrimary = in_array($column->getName(), $primary);

            if ($column->isIdentity() && $isPrimary) {
                $sequence = $platform->getSequenceName($ddl->getName());
                $this->ensureValidIdentifierLength($sequence);

                $sql[] = 'CREATE SEQUENCE ' . $this->quoteIdentifier($sequence) . ' NOMAXVALUE';
                break;
            }
        }

        return $sql;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\DDLRenderer::renderForeignKeyAction()
     */
    protected function renderForeignKeyAction($type, $action)
    {
        if ($type != ReferenceDefinition::ON_DELETE) {
            return '';
        }

        $sql = 'ON DELETE ';
        switch ($action) {
            case ReferenceDefinition::ACTION_CASCADE:
                $sql .= 'CASCADE';
                break;

            case ReferenceDefinition::ACTION_SETNULL:
                $sql .= 'SET NULL';
                break;

            default:
                $sql = '';
                break;
        }

        return $sql;
    }




}
