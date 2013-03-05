<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2012 Axel Helmert
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

namespace rampage\orm\db\adapter;

use Zend\Db\Adapter\Adapter as DefaultAdapter;
use rampage\orm\db\adapter\oracle\PDODriver as OraclePDODriver;

/**
 * Adapter implementation
 */
class Adapter extends DefaultAdapter
{
	/**
     * (non-PHPdoc)
     * @see \Zend\Db\Adapter\Adapter::createDriver()
     */
    protected function createDriver($parameters)
    {
        if (!is_string($parameters['driver']) || (strtolower($parameters['driver']) != 'pdo_oci')) {
            return parent::createDriver($parameters);
        }

        $options = array();
        if (isset($parameters['options'])) {
            $options = (array) $parameters['options'];
            unset($parameters['options']);
        }

        $driver = new OraclePDODriver($parameters);
        return $driver;
    }
}