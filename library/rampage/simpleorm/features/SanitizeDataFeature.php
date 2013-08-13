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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm\features;

use Zend\Db\TableGateway\Feature\AbstractFeature;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;

/**
 * Populate the id field
 */
class SanitizeDataFeature extends AbstractFeature
{
    /**
     * @param Insert $insert
     */
    public function preInsert(Insert $insert)
    {
        $allowedCols = $this->tableGateway->getColumns();
        if (!$allowedCols) {
            return;
        }

        $columns = $insert->getRawState('columns');
        foreach ($columns as $column) {
            if (!in_array($column, $allowedCols)) {
                unset($insert->{$column});
            }
        }

        return $this;
    }

    /**
     * @param Update $update
     */
    public function preUpdate(Update $update)
    {
        $columns = $this->tableGateway->getColumns();
        if (!$columns) {
            return;
        }

        $data = $update->getRawState('set');
        foreach ($data as $key => $value) {
            if (!in_array($key, $columns)) {
                unset($data[$key]);
            }
        }

        $update->set($data, Update::VALUES_SET);
        return $this;
    }
}