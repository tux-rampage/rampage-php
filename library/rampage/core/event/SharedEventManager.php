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
    protected $configuredEvents = array();

    /**
     * Flag if configs can be added
     *
     * @var bool
     */
    private $canAddConfig = true;

    /**
     * Config instance
     *
     * @var ConfigInterface
     */
    private $config = null;

    /**
     * @param \rampage\core\event\ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Add config events
     *
     * @param string $id
     * @param string $event
     */
    protected function addConfigListeners($id, $event)
    {
        if (isset($this->configuredEvents[$id][$event]) || !$this->canAddConfig || !$this->config) {
            return $this;
        }

        // Disable config load now to avoid being triggerd recursively by inner events
        $this->canAddConfig = false;

        $this->config->configureEventManager($this, $id, $event);
        $this->configuredEvents[$id][$event] = true;

        // Add wildcard events if not done, yet
        if (!isset($this->configuredEvents[$id]['*'])) {
            $this->config->configureEventManager($this, $id, '*');
            $this->configuredEvents[$id]['*'] = true;
        }

        $this->canAddConfig = true;
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