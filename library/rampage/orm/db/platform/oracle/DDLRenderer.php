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
        ColumnDefinition::TYPE_TEXT => 'VARCHAR2',
        ColumnDefinition::TYPE_VARCHAR => 'VARCHAR2',
        ColumnDefinition::TYPE_DATE => 'DATE',
        ColumnDefinition::TYPE_DATETIME => 'DATE',
    );

    /**
     * @see \rampage\orm\db\platform\DDLRenderer::formatIdentifier()
     */
    protected function formatIdentifier($identifier)
    {
        return strtoupper($identifier);
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
        $spec = parent::getColumnSpec($column, $ddl);

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
}
