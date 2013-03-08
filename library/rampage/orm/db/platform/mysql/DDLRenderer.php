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
namespace rampage\orm\db\platform\mysql;

use rampage\orm\db\platform\DDLRenderer as DefaultDDLRenderer;
use rampage\orm\db\ddl\AlterTable;
use rampage\orm\db\ddl\ColumnDefinition;
use rampage\orm\db\ddl\ChangeColumn;
use rampage\orm\db\ddl\IndexDefinition;
use rampage\orm\db\ddl\AbstractTableDefinition;
use rampage\orm\db\platform\PlatformInterface;

/**
 * DDL renderer
 */
class DDLRenderer extends DefaultDDLRenderer
{
    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\DDLRenderer::__construct()
     */
    public function __construct(PlatformInterface $platform)
    {
        $this->columnTypeMap[ColumnDefinition::TYPE_BOOL] = 'TINYINT';
        $this->columnTypeMap[ColumnDefinition::TYPE_CLOB] = 'LONGTEXT';

        parent::__construct($platform);
    }

	/**
     * @see \rampage\orm\db\platform\DDLRenderer::renderAlterColumn()
     */
    protected function renderAlterColumn(AlterTable $ddl, ChangeColumn $column)
    {
        if ($column->getNewName()) {
            $sql = "CHANGE COLUMN {$this->renderFieldName($ddl, $column)} "
                 . $this->renderFieldName($ddl, $column->getNewName());
        } else {
            $sql = "MODIFY COLUMN {$this->renderFieldName($ddl, $column)}";
        }

        $sql .= $this->renderColumnDefintion($column, $ddl);

        if ($column->isFirst()) {
            $sql .= ' FIRST';
        } else if ($column->getAfter()) {
            $sql .= ' AFTER ' . $this->renderFieldName($ddl, $column->getAfter());
        }

        return $sql;
    }

    /**
     * @see \rampage\orm\db\platform\DDLRenderer::renderDropForeignKey()
     */
    protected function renderDropForeignKey(AlterTable $ddl, $name)
    {
        return "DROP FOREIGN KEY {$this->renderKeyName($name)}";
    }

    /**
     * @see \rampage\orm\db\platform\DDLRenderer::renderDropIndex()
     */
    protected function renderDropIndex(AlterTable $ddl, $name)
    {
        return "DROP INDEX {$this->renderKeyName($name)}";
    }

    /**
     * @see \rampage\orm\db\platform\DDLRenderer::renderIndex()
     */
    protected function renderIndex(IndexDefinition $index, AbstractTableDefinition $ddl)
    {
        $fields = $this->renderFieldList($ddl, $index->getFields());
        $type = ($index->isUnique())? 'UNIQUE' : 'INDEX';

        return "$type {$this->renderKeyName($index->getName())} ($fields)";
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\DDLRenderer::getColumnExtra()
     */
    protected function getColumnExtra(ColumnDefinition $column, AbstractTableDefinition $ddl)
    {
        $extra = '';

        if ($this->isAutoIncrementColumn($column)) {
            $extra = 'AUTO_INCREMENT';
        }

        return $extra;
    }

    /**
     * Check for auto incrment column
     *
     * @param ColumnDefinition $column
     * @return boolean
     */
    protected function isAutoIncrementColumn(ColumnDefinition $column)
    {
        $result = ($column->isIdentity() && ($column->getType() == ColumnDefinition::TYPE_INT));
        return $result;
    }

    /**
     * Check if column is numeric
     *
     * @param ColumnDefinition $column
     * @return boolean
     */
    protected function isNumericColumn(ColumnDefinition $column)
    {
        return in_array($column->getType(), array(
            ColumnDefinition::TYPE_FLOAT,
            ColumnDefinition::TYPE_INT
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\DDLRenderer::getColumnTypeExtra()
     */
    protected function getColumnTypeExtra(ColumnDefinition $column, AbstractTableDefinition $ddl)
    {
        $extra = '';

        if ($column->isUnsigned() && $this->isNumericColumn($column)) {
            $extra = 'UNSIGNED';
        }

        return $extra;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\DDLRenderer::getColumnSpec()
     */
    protected function getColumnSpec(ColumnDefinition $column, AbstractTableDefinition $ddl)
    {
        $spec = parent::getColumnSpec($column, $ddl);

        if ($this->isAutoIncrementColumn($column)) {
            $spec['default'] = '';
        }

        return $this;
    }

    /**
     * Table options
     *
     * @param AbstractTableDefinition $ddl
     * @return array
     */
    protected function getTableOptions(AbstractTableDefinition $ddl)
    {
        $options = array();

        foreach ($ddl->getOptions() as $name => $value) {
            switch (strtolower($name)) {
                case 'mysql_engine':
                    $options['engine'] = 'ENGINE = ' . $value;
                    break;

                case 'comment':
                    $options['comment'] = 'COMMENT = ' . strtolower($value);
                    break;

            }
        }

        $options['charset'] = 'CHARACTER SET = ' . $ddl->getCharset();
        return $options;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\DDLRenderer::renderAlterTableOptions()
     */
    protected function renderAlterTableOptions(AbstractTableDefinition $ddl)
    {
        $options = $this->getTableOptions($ddl);
        return implode(' ', $options);
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\DDLRenderer::renderCreateTableOptions()
     */
    protected function renderCreateTableOptions(AbstractTableDefinition $ddl)
    {
        $options = $this->getTableOptions($ddl);
        return implode(' ', $options);
    }
}