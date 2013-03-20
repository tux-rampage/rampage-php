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

// Exceptions
use rampage\orm\exception\RuntimeException;
use rampage\orm\exception\InvalidArgumentException;

// DDL Classes
use rampage\orm\db\ddl\DefinitionInterface;
use rampage\orm\db\ddl\CreateTable;
use rampage\orm\db\ddl\NamedDefintion;
use rampage\orm\db\ddl\ColumnDefinition;
use rampage\orm\db\ddl\AbstractTableDefinition;
use rampage\orm\db\ddl\IndexDefinition;
use rampage\orm\db\ddl\ReferenceDefinition;
use rampage\orm\db\ddl\AlterTable;
use rampage\orm\db\ddl\ChangeColumn;
use rampage\orm\db\ddl\DropTable;

/**
 * DDL Renderer
 */
class DDLRenderer implements DDLRendererInterface
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
        ColumnDefinition::TYPE_VARCHAR => 'VARCHAR',
        ColumnDefinition::TYPE_DATE => 'DATE',
        ColumnDefinition::TYPE_DATETIME => 'DATETIME',
    );

    /**
     * Action type map
     *
     * @var string
     */
    protected $fkActionTypeMap = array(
        ReferenceDefinition::ON_UPDATE => 'ON UPDATE',
        ReferenceDefinition::ON_DELETE => 'ON DELETE'
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
     * @param NamedDefintion $ddlOrName
     * @return string
     */
    protected function renderTableName($ddlOrName)
    {
        if ($ddlOrName instanceof NamedDefintion) {
            $ddlOrName = $ddlOrName->getName();
        }

        return $this->quoteIdentifier($this->getTable($ddlOrName));
    }

    /**
     * Create table options
     *
     * @param AbstractTableDefinition $ddl
     * @return string
     */
    protected function renderCreateTableOptions(AbstractTableDefinition $ddl)
    {
        return '';
    }

    /**
     * Alter table options
     *
     * @param AbstractTableDefinition $ddl
     * @return string
     */
    protected function renderAlterTableOptions(AbstractTableDefinition $ddl)
    {
        return '';
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

        if ($entity instanceof AbstractTableDefinition) {
            $entity = $entity->getName();
        }

        return $this->quoteIdentifier($this->getFieldName($entity, $attribute));
    }

    /**
     * Render identifier
     *
     * @param string $name
     * @return string
     */
    protected function renderIdentifier($name)
    {
        return $this->quoteIdentifier($this->formatIdentifier($name));
    }

    /**
     * Render the key name
     *
     * @param string $name
     * @return string
     */
    protected function renderKeyName($name)
    {
        return strtoupper($name);
    }

    /**
     * Column extra definition
     *
     * @param ColumnDefinition $column
     * @param AbstractTableDefinition $ddl
     */
    protected function getColumnExtra(ColumnDefinition $column, AbstractTableDefinition $ddl)
    {
        return '';
    }

    /**
     * Column extra definition
     *
     * @param ColumnDefinition $column
     * @param AbstractTableDefinition $ddl
     */
    protected function getColumnTypeExtra(ColumnDefinition $column, AbstractTableDefinition $ddl)
    {
        return '';
    }

    /**
     * Check if null is allowed for the given column
     *
     * @param ColumnDefinition $column
     * @param AbstractTableDefinition $table
     */
    protected function isColumnNullable(ColumnDefinition $column, AbstractTableDefinition $table)
    {
        if (!$column->isNullable()) {
            return false;
        }

        $primary = $table->getPrimaryKey();
        $nullable = ((count($primary) > 1) || !in_array($column->getName(), $primary));

        return $nullable;
    }

    /**
     * Render column definition
     *
     * @param ColumnDefinition $column
     */
    protected function getColumnSpec(ColumnDefinition $column, AbstractTableDefinition $ddl)
    {
        if (!isset($this->columnTypeMap[$column->getType()])) {
            throw new InvalidArgumentException('Unsupported column type: ' . $column->getType());
        }

        $type = $this->columnTypeMap[$column->getType()];
        switch ($column->getType()) {
            case ColumnDefinition::TYPE_VARCHAR:
                $size = ($column->getSize())?: 255;
                $type .= '(' . $size . ')';
                break;

            case ColumnDefinition::TYPE_BOOL:
                $type .= '(1)';
                break;

            case ColumnDefinition::TYPE_ENUM:
                $type .= '(' . $this->getPlatform()->getAdapterPlatform()->quoteValueList($column->getValues()) . ')';
                break;

            case ColumnDefinition::TYPE_FLOAT:
                $type .= '(' . $column->getSize(true) . ', ' . $column->getPrecision(true) . ')';
                break;
        }

        $extra = $this->getColumnExtra($column, $ddl);
        $typeExtra = $this->getColumnTypeExtra($column, $ddl);
        $isNullable = $this->isColumnNullable($column, $ddl);
        $nullable = ($isNullable)? 'NULL' : 'NOT NULL';
        $default = '';

        if ($isNullable || ($column->getDefault() !== null)) {
            $default = 'DEFAULT ' . $this->getPlatform()->getAdapterPlatform()->quoteValue($column->getDefault());
        }

        return compact('type', 'typeExtra', 'nullable', 'default', 'extra');
    }

    /**
     * Render column defintion
     *
     * @param ColumnDefinition $column
     * @return string
     */
    protected function renderColumnDefintion(ColumnDefinition $column, AbstractTableDefinition $ddl)
    {
        $spec = $this->getColumnSpec($column, $ddl);
        $spec = array_filter($spec);

        return implode(' ', $spec);
    }

    /**
     * Map field list
     *
     * @param string $entity
     * @param array $attributes
     * @return string[]
     */
    protected function mapFieldList($entity, array $attributes)
    {
        $fields = array();

        foreach ($attributes as $attribute) {
            $fields[] = $this->renderFieldName($entity, $attribute);
        }

        return $fields;
    }

    /**
     * Render field list
     *
     * @param string $entity
     * @param array $attributes
     * @return string
     */
    protected function renderFieldList($entity, array $attributes)
    {
        return implode(', ', $this->mapFieldList($entity, $attributes));
    }

    /**
     * Render the primary key part
     *
     * @param AbstractTableDefinition $ddl
     * @return string
     */
    protected function renderPrimaryKey(AbstractTableDefinition $ddl)
    {
        $primary = $this->mapFieldList($ddl, $ddl->getPrimaryKey());

        if (empty($primary)) {
            return '';
        }

        return 'PRIMARY KEY (' . implode(', ', $primary) . ')';
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

            if ($order) {
                $field .= ' ' . $order;
            }

            $fields[] = $field;
        }

        if (empty($fields)) {
            return '';
        }

        $fields = implode(', ', $fields);
        return $fields;
    }

    /**
     * Render index
     *
     * @param IndexDefinition $index
     * @return string
     */
    protected function renderIndex(IndexDefinition $index, AbstractTableDefinition $ddl)
    {
        $name = $this->renderKeyName($index->getName());
        $fields = $this->renderIndexFields($index, $ddl);

        if (empty($fields)) {
            return '';
        }

        if ($index->isUnique()) {
            $sql = "CONSTRAINT $name UNIQUE ($fields)";
        } else {
            $sql = "INDEX $name ($fields)";
        }

        return $sql;
    }

    /**
     * Render foreign key action
     *
     * @param string $type
     * @param string $action
     */
    protected function renderForeignKeyAction($type, $action)
    {
        if (!array_key_exists($type, $this->fkActionTypeMap)) {
            return '';
        }

        $sql = $this->fkActionTypeMap[$type];

        switch ($action) {
            case ReferenceDefinition::ACTION_CASCADE:
            case ReferenceDefinition::ACTION_RESTRICT:
                $sql .= ' ' . strtoupper($action);
                break;

            case ReferenceDefinition::ACTION_NOACTION:
                $sql .= ' NO ACTION';
                break;

            case ReferenceDefinition::ACTION_SETNULL:
                $sql .= ' SET NULL';
                break;

            default:
                $sql = '';
                break;
        }

        return $sql;
    }

    /**
     * Render foreign key
     *
     * @param ReferenceDefinition $reference
     * @param AbstractTableDefinition $ddl
     * @return string
     */
    protected function renderForeignKey(ReferenceDefinition $reference, AbstractTableDefinition $ddl)
    {
        $name = $this->renderKeyName($reference->getName());
        $table = $this->renderTableName($reference->getReferenceEntity());
        $fields = $this->mapFieldList($ddl, $reference->getFields());
        $referenceFields = $this->mapFieldList($reference->getReferenceEntity(), $reference->getReferenceFields());

        $fields = implode(', ', $fields);
        $referenceFields = implode(', ', $referenceFields);

        $sql = "CONSTRAINT $name FOREIGN KEY ($fields) "
             . "REFERENCES $table ($referenceFields)";

        foreach ($reference->getActions() as $type => $action) {
            $actionSql = $this->renderForeignKeyAction($type, $action);

            if ($actionSql) {
                $sql .= ' ' . $actionSql;
            }
        }

        return $sql;
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
            $parts[] = $this->renderFieldName($ddl->getName(), $column->getName())
                     . ' ' . $this->renderColumnDefintion($column, $ddl);
        }

        $parts[] = $this->renderPrimaryKey($ddl);

        foreach ($ddl->getIndexes() as $index) {
            $parts[] = $this->renderIndex($index, $ddl);
        }

        foreach ($ddl->getReferences() as $reference) {
            $parts[] = $this->renderForeignKey($reference, $ddl);
        }

        // Ensure there are no empty parts
        $parts = array_map('trim', $parts);
        $parts = array_filter($parts);

        $sql = "CREATE TABLE {$this->renderTableName($ddl)} (\n\t"
             . implode(",\n\t", $parts)
             . "\n) {$this->renderCreateTableOptions($ddl)}";

        return $sql;
    }

    /**
     * Render add column
     *
     * @param AlterTable $ddl
     * @param ChangeColumn $column
     * @return string
     */
    protected function renderAddColumn(AlterTable $ddl, ChangeColumn $column)
    {
        return 'ADD COLUMN ' . $this->renderFieldName($ddl, $column) .
               $this->renderColumnDefintion($column, $ddl);
    }

    /**
     * Change column renderer
     *
     * @param AlterTable $ddl
     * @param ChangeColumn $column
     * @return string
     */
    protected function renderAlterColumn(AlterTable $ddl, ChangeColumn $column)
    {
        $colName = $this->renderFieldName($ddl, $column->getName());
        $sql = "ALTER COLUMN $colName {$this->renderColumnDefintion($column, $ddl)}";

        if ($column->getNewName()) {
            $newColName = $this->renderFieldName($ddl, $column->getNewName());
            $sql = "$newColName,\nRENAME COLUMN $colName TO $newColName";
        }

        return $sql;
    }

    /**
     * Render drop column
     *
     * @param AlterTable $ddl
     * @param ChangeColumn $column
     * @return string
     */
    protected function renderDropColumn(AlterTable $ddl, ChangeColumn $column)
    {
        $sql = "DROP COLUMN {$this->renderFieldName($ddl, $column)}";
        return $sql;
    }

    /**
     * Render drop constraint
     *
     * @param AlterTable $ddl
     * @param unknown $name
     * @return string
     */
    protected function renderDropForeignKey(AlterTable $ddl, $name)
    {
        return "DROP CONSTRAINT {$this->renderKeyName($name)}";
    }

    /**
     * Render drop constraint
     *
     * @param AlterTable $ddl
     * @return string
     */
    protected function renderDropPrimaryKey(AlterTable $ddl)
    {
        return "DROP PRIMARY KEY";
    }

    /**
     * Render drop index
     *
     * @param AlterTable $ddl
     * @param string $name
     * @return string
     */
    protected function renderDropIndex(AlterTable $ddl, $name)
    {
        if ($name instanceof IndexDefinition) {
            if ($name->isUnique()) {
                $name = $name->getName();
                return "DROP CONSTRAINT {$this->renderKeyName($name)}";
            }

            $name = $name->getName();
        }

        return "DROP INDEX {$this->renderKeyName($name)}";
    }

    /**
     * Render add index
     *
     * @param AlterTable $ddl
     * @param IndexDefinition $index
     * @return string
     */
    protected function renderAddIndex(AlterTable $ddl, IndexDefinition $index)
    {
        return "ADD {$this->renderIndex($index, $ddl)}";
    }

    /**
     * Add foreign key
     *
     * @param AlterTable $ddl
     * @param ReferenceDefinition $reference
     * @return string
     */
    protected function renderAddForeignKey(AlterTable $ddl, ReferenceDefinition $reference)
    {
        return "ADD {$this->renderForeignKey($reference, $ddl)}";
    }

    /**
     * Render add primary key
     *
     * @param AlterTable $ddl
     * @return string
     */
    protected function renderAddPrimaryKey(AlterTable $ddl)
    {
        $def = $this->renderPrimaryKey($ddl);
        if (!$def) {
            return '';
        }

        return "ADD $def";
    }

    /**
     * Render alter table
     *
     * @param AlterTable $ddl
     * @return string
     */
    public function renderAlterTable(AlterTable $ddl)
    {
        $parts = array();

        foreach ($ddl->getColumns() as $column) {
            if (!$column instanceof ChangeColumn) {
                continue;
            }

            switch ($column->getChangeType()) {
                case ChangeColumn::CHANGETYPE_ADD:
                    $parts[] = $this->renderAddColumn($ddl, $column);
                    break;

                case ChangeColumn::CHANGETYPE_DROP:
                    $parts[] = $this->renderAlterColumn($ddl, $column);
                    break;

                case ChangeColumn::CHANGETYPE_DROP:
                    $parts[] = $this->renderDropColumn($ddl, $column);
                    break;
            }
        }

        foreach ($ddl->getDropElements() as $type => $drop) {
            switch ($type) {
                case AlterTable::DROP_TYPE_INDEX:
                    foreach ($drop as $dropIndex) {
                        $parts[] = $this->renderDropIndex($ddl, $dropIndex);
                    }

                    break;

                case AlterTable::DROP_TYPE_REFERENCE:
                    foreach ($drop as $dropReference) {
                        $parts[] = $this->renderDropForeignKey($ddl, $dropReference);
                    }

                    break;

                case AlterTable::DROP_TYPE_PRIMARY:
                    $parts[] = $this->renderDropPrimaryKey($ddl);
            }
        }

        $parts[] = $this->renderAddPrimaryKey($ddl);

        foreach ($ddl->getIndexes() as $index) {
            $parts[] = $this->renderAddIndex($ddl, $index);
        }

        foreach ($ddl->getReferences() as $reference) {
            $parts[] = $this->renderAddForeignKey($ddl, $reference);
        }

        $parts[] = $this->renderAlterTableOptions($ddl);

        // Do not allow empty parts to prevent "... , , ..."
        $parts = array_map('trim', $parts);
        $parts = array_filter($parts);
        $parts = implode(",\n", $parts);

        $sql = "ALTER TABLE {$this->renderTableName($ddl)}\n$parts";
        return $sql;
    }

    /**
     * Render drop table
     *
     * @param DropTable $ddl
     * @return string
     */
    public function renderDropTable(DropTable $ddl)
    {
        return "DROP TABLE {$this->renderTableName($ddl)}";
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

            case AlterTable::DDL_NAME:
                return $this->renderAlterTable($ddl);

            case DropTable::DDL_NAME:
                return $this->renderDropTable($ddl);
        }

        throw new RuntimeException('Unsupported DDL statement: ' . $ddl->getDdlDefintionName());
    }


}