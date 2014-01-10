<?php
/**
 * This is part of rampage-php
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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\ui;

use IteratorAggregate;
use ArrayIterator;

/**
 * Container for toast elements
 */
class ToastContainer implements IteratorAggregate
{
    /**
     * @var array
     */
    protected $items = array();

    /**
     * {@inheritdoc}
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @param Toast $toast
     * @return self
     */
    public function add(Toast $toast)
    {
        $this->items[] = $toast;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = array();

        foreach ($this->items as $toast) {
            $result[] = array(
                'message' => $toast->getMessage(),
                'options' => $toast->getOptions()
            );
        }

        return $result;
    }
}
