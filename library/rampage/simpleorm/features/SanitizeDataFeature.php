<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2012 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 * @version   $Id$
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