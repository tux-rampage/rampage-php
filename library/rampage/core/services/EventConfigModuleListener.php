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

namespace rampage\core\services;

use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\EventManager\SharedEventManager;

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
     * @param EventInterface $event
     */
    public function __invoke(ModuleEvent $event)
    {
        $manager = $event->getTarget();
        $eventManager = $this->serviceManager->get('SharedEventManager');

        if (!($manager instanceof ModuleManager) || !($eventManager instanceof SharedEventManager)) {
            return;
        }

        foreach ($manager->getLoadedModules() as $module) {
            if (!$module instanceof ListenerProviderInterface) {
                continue;
            }

            foreach ($module->getEvents() as $scope) {
                foreach ($scope as $event => $listeners) {
                    foreach ($listeners as $listener) {
                        $priority = 1;

                        if (isset($listener['listener'])) {
                            $priority = (isset($listener['priority']))? (int)$listener['priority'] : 1;
                            $listener = $listener['listener'];
                        }

                        if ($listener instanceof ServiceManagerAwareInterface) {
                            $listener->setServiceManager($this->serviceManager);
                        }

                        $eventManager->attach($scope, $event, $listener, $priority);
                    }
                }
            }
        }
    }
}
