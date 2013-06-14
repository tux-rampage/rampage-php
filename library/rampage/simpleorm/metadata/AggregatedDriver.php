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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm\metadata;

/**
 * Aggergated metadata driver
 */
class AggregatedDriver implements DriverInterface
{
    /**
     * @var SplPriorityQueue
     */
    private $drivers = array();

    /**
     * @param DriverInterface $driver
     * @return \rampage\simpleorm\metadata\AggregatedDriver
     */
    public function addDriver(DriverInterface $driver)
    {
        $this->drivers[] = $driver;
        return $this;
    }

    /**
     * Construct
     */
    public function __construct()
    {
        $this->addDriver(new ReflectionDriver())
            ->addDriver(new DatabaseDriver());
    }

    /**
     * @see \rampage\simpleorm\metadata\DriverInterface::hasEntityDefintion()
     */
    public function hasEntityDefintion($name)
    {
        foreach ($this->drivers as $driver) {
            if ($driver->hasEntityDefintion($name)) {
                return true;
            }
        }

        return false;
    }

	/**
     * @see \rampage\simpleorm\metadata\DriverInterface::loadEntityDefintion()
     */
    public function loadEntityDefintion($name, Metadata $metadata, Entity $entity = null)
    {
        /* @var $driver DriverInterface */
        foreach ($this->drivers as $driver) {
            $driver->loadEntityDefintion($name, $metadata, $entity);
        }

        return $this;
    }
}