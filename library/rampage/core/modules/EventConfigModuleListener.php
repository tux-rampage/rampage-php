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

namespace rampage\core\modules;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\SharedEventManagerInterface;

use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Events Module listener
 */
class EventConfigModuleListener
{
    /**
     * @var ServiceManager
     */
    private $serviceManager = null;

    /**
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param object $listener
     * @return self
     */
    protected function injectServiceManager($listener)
    {
        if ($listener instanceof ServiceManagerAwareInterface) {
            $listener->setServiceManager($this->serviceManager);
        }

        if ($listener instanceof ServiceLocatorAwareInterface) {
            $listener->setServiceLocator($this->serviceManager);
        }

        return $this;
    }

    /**
     * @param SharedEventManagerInterface $eventManager
     * @param string $events
     */
    protected function attachListeners(SharedEventManagerInterface $eventManager, $id, $config)
    {
        if (!isset($config['listeners'])) {
            return $this;
        }

        foreach ($config['listeners'] as $event => $listeners) {
            foreach ($listeners as $listener) {
                $priority = 1;

                if (isset($listener['listener'])) {
                    $priority = (isset($listener['priority']))? (int)$listener['priority'] : 1;
                    $listener = $listener['listener'];
                }

                $this->injectServiceManager($listener);
                $eventManager->attach($id, $event, $listener, $priority);
            }
        }


        return $this;
    }

    /**
     * @param SharedEventManagerInterface $eventManager
     * @param string $id
     * @param unknown $config
     * @return \rampage\core\services\EventConfigModuleListener
     */
    protected function attachAggregates(SharedEventManagerInterface $eventManager, $id, $config)
    {
        if (!isset($config['aggregates'])) {
            return $this;
        }

        foreach ($config['aggregates'] as $listenerAggregate) {
            if (is_string($listenerAggregate) && class_exists($listenerAggregate)) {
                $listenerAggregate = $this->serviceManager->get('di')->newInstance($listenerAggregate);
            }

            if (!$listenerAggregate instanceof ListenerAggregateInterface) {
                continue;
            }

            $this->injectServiceManager($listenerAggregate);
            $eventManager->attach($id, $listenerAggregate, null);
        }

        return $this;
    }

    /**
     * @param EventInterface $event
     */
    public function __invoke(ModuleEvent $event)
    {
        $moduleManager = $event->getTarget();
        $eventManager = $this->serviceManager->get('SharedEventManager');

        if (!($moduleManager instanceof ModuleManager) || !($eventManager instanceof SharedEventManagerInterface)) {
            return;
        }

        foreach ($moduleManager->getLoadedModules() as $module) {
            if (!$module instanceof EventListenerProviderInterface) {
                continue;
            }

            foreach ($module->getEventListeners() as $id => $config) {
                $this->attachListeners($eventManager, $id, $config)
                    ->attachAggregates($eventManager, $id, $config);
            }
        }
    }
}
