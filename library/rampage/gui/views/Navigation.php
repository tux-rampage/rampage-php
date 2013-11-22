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
 * @package   rampage.gui
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\gui\views;

use rampage\core\view\Template;
use Zend\Mvc\MvcEvent;

/**
 * Navigation view
 */
class Navigation extends Template
{
    /**
     * Navigation items
     *
     * @var NavigationItem[]
     */
    protected $items = array();

    /**
     * Check mvc event
     *
     * @return boolean
     */
    public function hasMvcEvent()
    {
        return ($this->getMvcEvent() !== null);
    }

    /**
     * MVC Event
     *
     * @return \Zend\Mvc\MvcEvent|null
     */
    public function getMvcEvent()
    {
        $event = $this->getLayout()->getData()->get('mvc_event');
        if (!$event instanceof MvcEvent) {
            return null;
        }

        return $event;
    }

    /**
     * Add a route link
     *
     * @param string $route
     * @param string $label
     * @param array $params
     */
    public function addRouteLink($id, $route, $label, array $params = array())
    {
        $this->items[$id] = new NavigationItem($id, $route, $label, $params);
        return $this;
    }

    /**
     * @return null|string
     */
    protected function getCurrentRouteName()
    {
        if (!$this->hasMvcEvent()) {
            return null;
        }

        $match = $this->getMvcEvent()->getRouteMatch();
        $name = ($match)? $match->getMatchedRouteName() : null;

        return $name;
    }

    /**
     * @param NavigationItem $item
     * @return boolean
     */
    public function isActive(NavigationItem $item, $partial = true)
    {
        $current = $this->getCurrentRouteName();
        if (!$current || ($item->getType() != NavigationItem::TYPE_ROUTE)) {
            return false;
        }

        $result = ($item->getRoute() == $current) || ($partial && (strpos($current, $item->getRoute() . '/') === 0));
        return $result;
    }


    /**
     * Returns all items
     *
     * @return NavigationItem[]
     */
    public function getItems()
    {
        return $this->items;
    }
}
