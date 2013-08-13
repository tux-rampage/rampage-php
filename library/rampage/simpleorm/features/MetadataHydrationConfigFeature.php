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

use rampage\simpleorm\RepositoryInterface;
use rampage\simpleorm\hydration;

use Zend\Db\TableGateway\Feature\MetadataFeature;
use Zend\Stdlib\Hydrator\StrategyEnabledInterface;
use Zend\Db\Metadata\Metadata;
use Zend\Stdlib\Hydrator\AbstractHydrator;

/**
 * Hydrator config feature
 */
class MetadataHydrationConfigFeature extends MetadataFeature
{
    /**
     * @see \Zend\Db\TableGateway\Feature\AbstractFeature::initialize()
     */
    public function postInitialize()
    {
        if (!$this->tableGateway instanceof RepositoryInterface) {
            return;
        }

        $hydrator = $this->tableGateway->getHydrator();

        if ($hydrator instanceof AbstractHydrator) {
            $filter = new hydration\InArrayFilter($this->tableGateway->getColumns());
            $hydrator->addFilter('fieldnames', $filter);
        }

        if (!$hydrator instanceof StrategyEnabledInterface) {
            return;
        }

        if ($this->metadata == null) {
            $this->metadata = new Metadata($this->tableGateway->adapter);
        }

        $columns = $this->metadata->getColumns($this->tableGateway->table);

        /* @var $column \Zend\Db\Metadata\Object\ColumnObject */
        foreach ($columns as $column) {
            if ($hydrator->hasStrategy($column->getName())) {
                continue;
            }

            switch (strtolower($column->getDataType())) {
                case 'date': // break intentionally omitted
                case 'time': // break intentionally omitted
                case 'datetime': // break intentionally omitted
                case 'timestamp':
                    $hydrator->addStrategy($column->getName(), new hydration\DateTimeStrategy());
                    break;

                case 'int':
                case 'integer':
                    $hydrator->addStrategy($column->getName(), new hydration\IntStrategy());
                    break;

                case 'decimal':
                case 'double':
                    $hydrator->addStrategy($column->getName(), new hydration\DecimalStrategy());
                    break;

                case 'number':
                    $strategy = ($column->getNumericPrecision() > 0)? new hydration\DecimalStrategy() : new hydration\IntStrategy();
                    $hydrator->addStrategy($column->getName(), $strategy);
                    break;
            }
        }
    }
}