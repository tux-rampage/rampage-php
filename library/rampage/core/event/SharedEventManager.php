<?php
/**
 * This is part of @application_name@
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
 * @package   @package_name@
 * @author    Axel Helmert
 * @copyright Copyright (c) 2012 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\event;

use Zend\EventManager\EventManager;

use Zend\EventManager\SharedEventManager as DefaultSharedEventManager;

/**
 * Shared event manager
 */
class SharedEventManager extends DefaultSharedEventManager
{
    /**
     * Configured events
     *
     * @var array
     */
    protected $_configuredEvents = array();

    /**
     * Flag if configs can be added
     *
     * @var bool
     */
    private $_canAddConfig = true;

    /**
     * Get event manager
     *
     * @return \Zend\EventManager\EventManager
     */
    protected function getIdentifierEventManager($id)
    {
        if (!array_key_exists($id, $this->identifiers)) {
            $this->identifiers[$id] = new EventManager();
        }

        return $this->identifiers[$id];
    }

    /**
     * Add config events
     *
     * @param string $id
     * @param string $event
     */
    protected function addConfigListeners($id, $event)
    {
        if (isset($this->_configuredEvents[$id][$event]) || !$this->_canAddConfig) {
            return $this;
        }

        // FIXME: implement config event loading
        return $this;

        // Disable config load now to avoid being triggerd recursively by inner events
        $this->_canAddConfig = false;
        $listeners = array();

        foreach ($listeners as $listener) {
            $this->getIdentifierEventManager($id)->attach($event, $listener->getCallback(), $listener->getPriority());
        }

        $this->_canAddConfig = true;
        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\EventManager\SharedEventManager::getListeners()
     */
    public function getListeners($id, $event)
    {
        $this->addConfigListeners($id, $event);
        return parent::getListeners($id, $event);
    }
}