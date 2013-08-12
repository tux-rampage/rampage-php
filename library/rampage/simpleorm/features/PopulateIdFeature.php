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

use rampage\simpleorm\DefaultTableGateway;
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
        if (!$this->tableGateway instanceof DefaultTableGateway) {
            return;
        }

        $feature = $this->tableGateway->getFeatureSet()->getFeatureByClassName('Zend\Db\TableGateway\Feature\MetadataFeature');
        if (!$feature || !isset($metadata->sharedData['metadata']['primaryKey'])) {
            return;
        }

        $this->tableGateway->setIdField($metadata->sharedData['metadata']['primaryKey']);
    }

    /**
     * @param StatementInterface $statement
     * @param ResultInterface $result
     */
    public function postInsert(StatementInterface $statement, ResultInterface $result)
    {
        if (!($this->tableGateway instanceof DefaultTableGateway) || !is_object($this->tableGateway->getCurrentObject())) {
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