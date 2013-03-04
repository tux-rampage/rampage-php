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
        ColumnDefinition::TYPE_TEXT => 'VARCHAR',
        ColumnDefinition::TYPE_VARCHAR => 'VARCHAR'
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
     * @see \rampage\orm\db\platform\DDLRenderer::renderAddColumn()
     */
    protected function renderAddColumn(AlterTable $ddl, ChangeColumn $column)
    {
        return "ADD ({$this->renderColumnDefintion($column, $ddl)})";
    }
}
