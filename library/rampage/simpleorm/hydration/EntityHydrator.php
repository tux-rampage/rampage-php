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

namespace rampage\simpleorm\hydration;

use Zend\Stdlib\Hydrator\AbstractHydrator;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Entity hydrator
 */
class EntityHydrator extends AbstractHydrator
{
    /**
     * @var unknown
     */
    private $metadata = null;

    /**
     * @see \Zend\Stdlib\Hydrator\AbstractHydrator::__construct()
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param object $object
     * @param array $data
     */
    protected function extractByHydrator(HydratorInterface $hydrator, $object, array &$data)
    {
        if ($hydrator instanceof AbstractHydrator) {
            $hydrator->strategies = $this->strategies;
        }

        $extracted = $hydrator->extract($object);
        $data = array_merge($data, $extracted);

        return $this;
    }

    /**
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::extract()
     */
    public function extract($object)
    {
        $data = array();

        $this->extractByHydrator(new ArraySerializableHydrator(), $object, $data)
            ->extractByHydrator(new ReflectionHydrator(), $object, $data);

        return $data;
    }

    /**
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        // TODO Auto-generated method stub

    }


}