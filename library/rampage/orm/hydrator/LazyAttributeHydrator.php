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

namespace rampage\orm\hydrator;

use rampage\orm\entity\lazy\EntityInterface as LazyEntityInterface;
use rampage\orm\exception\InvalidArgumentException;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Lazy attribute hydration
 */
class LazyAttributeHydrator implements HydratorInterface
{
    /**
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::extract()
     */
    public function extract($object)
    {
        return array();
    }

	/**
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        if (!$object instanceof LazyEntityInterface) {
            throw new InvalidArgumentException('The hydrated object must implement rampage.orm.entity.lazy.EntityInterface');
        }

        foreach ($data as $name => $delegate) {
            if (!is_callable($delegate)) {
                continue;
            }

            $object->addLazyAttribute($name, $delegate);
        }
    }
}
