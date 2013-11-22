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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\gui\views;

use rampage\core\data\ValueObject;

/**
 * Navigation item
 */
class NavigationItem
{
    const TYPE_ROUTE = 'route';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $type = self::TYPE_ROUTE;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var ValueObject
     */
    protected $options;

    /**
     * @param string $id
     * @param string $route
     * @param string $label
     * @param array $options
     */
    public function __construct($id, $route, $label, array $options = array())
    {
        $this->id = $id;
        $this->label = $label;
        $this->route = $route;
        $this->options = new ValueObject($options);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return \rampage\core\data\ValueObject
     */
    public function getOptions()
    {
        return $this->options;
    }
}
