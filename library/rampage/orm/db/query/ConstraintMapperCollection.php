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

namespace rampage\orm\query;

use ArrayObject;
use rampage\core\Utils;
use rampage\orm\db\platform\ConstraintMapperInterface;
use rampage\orm\exception\InvalidArgumentException;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Attribute constraint mapper collection
 */
class ConstraintMapperCollection extends ArrayObject implements ServiceLocatorInterface
{
    /**
     * @see ArrayObject::__construct()
     */
    public function __construct()
    {
        parent::__construct(array());
    }

	/**
     * @see ArrayObject::offsetSet()
     */
    public function offsetSet($index, $newval)
    {
        if (!$newval instanceof ConstraintMapperInterface) {
            throw new InvalidArgumentException(sprintf(
                'Invalid constraint mapper: %s does not implement use rampage.orm.db.platform.ConstraintMapperInterface',
                Utils::getPritableTypeName($newval)
            ));
        }

        return parent::offsetSet($newval);
    }

    /**
     * @see \Zend\ServiceManager\ServiceLocatorInterface::get()
     */
    public function get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * @see \Zend\ServiceManager\ServiceLocatorInterface::has()
     */
    public function has($name)
    {
        return $this->offsetExists($name);
    }
}