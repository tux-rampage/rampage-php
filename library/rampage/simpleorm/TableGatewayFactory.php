<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2012 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 * @version   $Id$
 */

namespace rampage\simpleorm;

use Zend\ServiceManager\FactoryInterface;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Stdlib\Hydrator\ArraySerializable as ArraySerializableHydrator;

use Zend\Db\TableGateway\Feature\MetadataFeature;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\ResultSet\HydratingResultSet;

/**
 * Table gateway factory
 */
class TableGatewayFactory implements FactoryInterface
{
    /**
     * @var string
     */
    private $table = null;

    /**
     * @var string|object
     */
    private $prototype = null;

    /**
     * @var \Zend\Db\TableGateway\Feature\AbstractFeature|\Zend\Db\TableGateway\Feature\FeatureSet|\Zend\Db\TableGateway\Feature\AbstractFeature[]
     */
    private $features = null;

    /**
     * @param string $table
     * @param object|string $prototype
     * @param \Zend\Db\TableGateway\Feature\AbstractFeature|\Zend\Db\TableGateway\Feature\FeatureSet|\Zend\Db\TableGateway\Feature\AbstractFeature[] $features
     */
    public function __construct($table, $prototype = null, $features = null)
    {
        $this->table = $table;
        $this->prototype = $prototype;

        if ($features === null) {
            $features = array(
                new MetadataFeature(),
                new features\PopulateIdFeature(),
                new features\SanitizeDataFeature()
            );
        } else if ($features === false) {
            $features = null;
        }

        $this->features = $features;
    }

    /**
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService($serviceLocator)
    {
        $resultSetPrototype = null;
        $hydrator = null;

        if ($this->prototype) {
            if (is_string($this->prototype)) {
                $class = $this->prototype;
                $this->prototype = new $class();
            }

            if ($this->prototype instanceof ResultSetInterface) {
                $resultSetPrototype = $this->prototype;
            } else if ($this->prototype instanceof ArraySerializableInterface) {
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype($this->prototype);

                $hydrator = new ArraySerializableHydrator();
            } else {
                $resultSetPrototype = new HydratingResultSet(
                    new ReflectionMappingHydrator(),
                    $this->prototype
                );
            }
        }

        $gateway = new DefaultTableGateway($this->table, $serviceLocator->get('db'), $this->features, $resultSetPrototype);
        if ($hydrator) {
            $gateway->setHydrator($hydrator);
        }

        return $gateway;
    }
}
