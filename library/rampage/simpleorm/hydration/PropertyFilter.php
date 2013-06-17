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

use Zend\Stdlib\Hydrator\Filter\FilterInterface;

/**
 * Field filter
 */
class PropertyFilter implements FilterInterface
{
    /**
     * Fields
     *
     * @var array
     */
    private $properties = array();

    /**
     * @param string $property
     */
    public function addProperty($property)
    {
        $this->properties[$property] = $property;
        return $this;
    }

    /**
     * @param string $property
     * @return \rampage\simpleorm\hydration\FieldFilter
     */
    public function removeProperty($property)
    {
        unset($this->properties[$property]);
        return $this;
    }

    /**
     * @see \Zend\Stdlib\Hydrator\Filter\FilterInterface::filter()
     */
    public function filter($property)
    {
        return isset($this->properties[$property]);
    }
}