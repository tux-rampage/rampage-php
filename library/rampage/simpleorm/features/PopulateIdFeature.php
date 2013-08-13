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

use rampage\simpleorm\TableGatewayRepository;
use Zend\Db\TableGateway\Feature\AbstractFeature;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * Populate the id field
 */
class PopulateIdFeature extends AbstractFeature
{
    /**
     * Post initialize
     */
    public function postInitialize()
    {
        if (!$this->tableGateway instanceof TableGatewayRepository) {
            return;
        }

        $feature = $this->tableGateway->getFeatureSet()->getFeatureByClassName('Zend\Db\TableGateway\Feature\MetadataFeature');
        if (!$feature || !isset($feature->sharedData['metadata']['primaryKey'])) {
            return;
        }

        $this->tableGateway->setIdField($feature->sharedData['metadata']['primaryKey']);
    }

    /**
     * @param StatementInterface $statement
     * @param ResultInterface $result
     */
    public function postInsert(StatementInterface $statement, ResultInterface $result)
    {
        if (!($this->tableGateway instanceof TableGatewayRepository) || !is_object($this->tableGateway->getCurrentObject())) {
            return;
        }

        $idField = $this->tableGateway->getIdField();
        if (!is_string($idField) || !$this->tableGateway->lastInsertValue) {
            return;
        }

        $data = array($idField => $this->tableGateway->lastInsertValue);
        $this->tableGateway->getHydrator()->hydrate($data, $this->tableGateway->getCurrentObject());
    }
}