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

namespace rampage\db;

use Zend\Db\Adapter\Adapter as DefaultAdapter;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Exception\InvalidArgumentException;
use Zend\Db\Adapter\Platform as DefaultPlatforms;

use rampage\db\driver\oracle\PDODriver as OraclePDODriver;
use rampage\db\platform\DriverAwareInterface;

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

    /**
     * @param Driver\DriverInterface $driver
     * @return Platform\PlatformInterface
     */
    protected function createPlatform($parameters)
    {
        if (isset($parameters['platform'])) {
            $platformName = $parameters['platform'];
        } elseif ($this->driver instanceof DriverInterface) {
            $platformName = $this->driver->getDatabasePlatformName(DriverInterface::NAME_FORMAT_CAMELCASE);
        } else {
            throw new InvalidArgumentException('A platform could not be determined from the provided configuration');
        }

        $options = (isset($parameters['platform_options'])) ? $parameters['platform_options'] : array();

        switch ($platformName) {
            case 'Mysql':
                $platform = new platform\Mysql($options);
                break;

            case 'SqlServer':
                $platform = new DefaultPlatforms\SqlServer($options);
                break;

            case 'Oracle':
                $platform = new platform\Oracle($options);
                break;

            case 'Sqlite':
                $platform = new DefaultPlatforms\Sqlite($options);
                break;

            case 'Postgresql':
                $platform = new DefaultPlatforms\Postgresql($options);
                break;

            case 'IbmDb2':
                $platform = new DefaultPlatforms\IbmDb2($options);
                break;

            default:
                $platform = new DefaultPlatforms\Sql92($options);
                break;
        }

        if (($this->driver instanceof DriverInterface) && ($platform instanceof DriverAwareInterface)) {
            $platform->setDriver($this->driver);
        }

        return $platform;
    }

}