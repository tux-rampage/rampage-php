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

namespace rampage\gui\view\html;

use rampage\core\view\Template;
use rampage\core\data\Object;
use Zend\Mvc\MvcEvent;

/**
 * Navigation view
 */
class Navigation extends Template
{
    /**
     * Navigation items
     *
     * @var array
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
        if (!$this->getLayout()->getData()->offsetExists('mvc_event')) {
            return null;
        }

        $event = $this->getLayout()->getData()->offsetGet('mvc_event');
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
        $item = new Object(array(
            'id' => $id,
            'type' => 'route',
            'route' => $route,
            'label' => $label,
            'options' => $params
        ));

        $this->items[$id] = $item;
        return $this;
    }

    public function isActive(Object $item)
    {
        if (($item->getType() != 'route') || !$this->hasMvcEvent()) {
            return false;
        }

        $event = $this->getMvcEvent();
        // TODO: Failsafe ...
        $result = ($item->getRoute() == $event->getRouteMatch()->getMatchedRouteName());
        return $result;
    }

    /**
     * Returns all items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }
}