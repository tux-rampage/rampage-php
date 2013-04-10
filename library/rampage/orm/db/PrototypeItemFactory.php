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

use rampage\orm\exception\InvalidArgumentException;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Prototype item factory
 */
class PrototypeItemFactory extends AbstractItemFactory
{
    /**
     * @var object
     */
    protected $prototype = null;

    /**
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
    private $hydrator = null;

    /**
     * @param HydratorInterface $hydrator
     * @param object $prototype
     * @throws InvalidArgumentException
     */
    public function __construct(HydratorInterface $hydrator, $prototype)
    {
        if (!is_object($prototype)) {
            throw new InvalidArgumentException('Invalid prototype: Object extected.');
        }

        parent::__construct($hydrator);
        $this->prototype = $prototype;
    }

    /**
     * @param array $data
     * @return bool|object
     */
    public function __invoke($data)
    {
        $data = $this->mapData($data);
        if (!$data) {
            return false;
        }

        $item = clone $this->prototype;
        $this->getHydrator()->hydrate($data, $item);

        return $item;
    }
}
