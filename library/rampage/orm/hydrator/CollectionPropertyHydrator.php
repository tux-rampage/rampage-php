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

use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;
use rampage\orm\entity\lazy\CollectionInterface as LazyCollectionInterface;
use rampage\orm\entity\lazy\delegate\CollectionLoaderInterface;
use rampage\orm\exception\LogicException;

/**
 * Collection reference hydrator
 */
class CollectionPropertyHydrator extends ReflectionHydrator
{
	/**
     * @see \Zend\Stdlib\Hydrator\Reflection::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        $reflectedProperties = self::getReflProperties($object);

        foreach ($data as $key => $value) {
            if (isset($reflectedProperties[$key])) {
                continue;
            }

            /* @var $property \ReflectionProperty */
            $property = $reflectedProperties[$key];

            // is lazy load expected?
            if ($value instanceof CollectionLoaderInterface) {
                $current = $property->getValue($object);
                if ($current instanceof LazyCollectionInterface) {
                    $current->setLoaderDelegate($value);
                }

                continue;
            }

            $class = (is_object($current))? get_class($current) : 'Traversable';
            if (!$value instanceof $class) {
                throw new LogicException(sprintf(
                    'Invalid collection: An instance of %s is expected while %s was given.',
                    strtr($class, '\\', '.'),
                    is_object($value)? strtr(get_class($value), '\\', '.') : gettype($value)
                ));
            }

            $property->setValue($object, $value);
        }
    }

    /**
     * @see \Zend\Stdlib\Hydrator\Reflection::extract()
     */
    public function extract($object)
    {
        return array();
    }
}