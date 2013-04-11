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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\db;

use Traversable;
use Zend\Stdlib\Hydrator\HydratorInterface;
use rampage\orm\exception\InvalidArgumentException;
use rampage\orm\db\platform\FieldMapper;

/**
 * Classname item factory
 */
abstract class AbstractItemFactory
{
    /**
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
    private $hydrator = null;

    /**
     * @var FieldMapper
     */
    private $fieldmapper = null;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param HydratorInterface $hydrator
     * @param string $classname
     * @throws InvalidArgumentException
     */
    public function __construct(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    protected function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * @return \rampage\orm\db\platform\FieldMapper
     */
    public function setFieldmapper(FieldMapper $mapper)
    {
        $this->fieldmapper = $mapper;
        return $this;
    }

    /**
     * Map data
     *
     * @param array $data
     * @return boolean
     */
    protected function mapData($data)
    {
        if (!is_array($data) || !($data instanceof Traversable)) {
            return false;
        }

        $result = array();
        $mapper = $this->fieldmapper?: function($key) { return $key; };

        foreach ($data as $key => $value) {
            $key = $mapper($key);
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @param array $data
     * @return bool|object
     */
    abstract public function __invoke($data);
}
