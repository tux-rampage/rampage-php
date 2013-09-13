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

namespace rampage\simpleorm;

use Zend\ServiceManager\FactoryInterface;
use ArrayObject;

/**
 * Table gateway factory
 */
class TableGatewayFactory implements FactoryInterface
{
    /**
     * @var string
     */
    protected $table = null;

    /**
     * @var string|object
     */
    protected $prototype = null;

    /**
     * @var \Zend\Db\TableGateway\Feature\AbstractFeature|\Zend\Db\TableGateway\Feature\FeatureSet|\Zend\Db\TableGateway\Feature\AbstractFeature[]
     */
    protected $features = null;

    /**
     * @var string
     */
    protected $adapterService = 'db';

    /**
     * @param string $table
     * @param object|string $prototype
     * @param \Zend\Db\TableGateway\Feature\AbstractFeature|\Zend\Db\TableGateway\Feature\FeatureSet|\Zend\Db\TableGateway\Feature\AbstractFeature[] $features
     */
    public function __construct($table, $prototype = null, $features = null, $adapterService = null)
    {
        $this->table = $table;
        $this->prototype = $prototype;
        $this->features = $features;

        if ($adapterService) {
            $this->adapterService = $adapterService;
        }
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
        } else {
            $prototype = new ArrayObject();
        }

        $gateway = new TableGatewayRepository($this->table, $serviceLocator->get($this->adapterService), $prototype, $this->features);
        return $gateway;
    }
}
