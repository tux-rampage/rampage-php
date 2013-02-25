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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\view\http;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use rampage\core\ObjectManagerInterface;

/**
 * View manager
 */
class ViewInitializer implements ListenerAggregateInterface
{
    /**
     * Attached listeners
     *
     * @var array
     */
    protected $listeners = array();

    /**
     * Service manager
     *
     * @var \rampage\core\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->setObjectManager($objectManager);
    }

    /**
     * Inject object manager
     *
     * @param \rampage\core\ObjectManagerInterface
     */
    public function setObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        return $this;
    }

    /**
     * Object manager
     *
     * @return \rampage\core\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $objectmanager = $this->getObjectManager();
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, $objectmanager->get('rampage.core.view.http.LayoutConfigListener'));
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'onBootstrap'));

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\EventManager\ListenerAggregateInterface::detach()
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }

        return $this;
    }

    /**
     * On bootstrap
     *
     * @param MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        /* @var $application \rampage\core\Application */
        $application = $event->getApplication();
        $eventManager = $application->getEventManager();

        $eventManager->attach($this->getObjectManager()->get('rampage.core.view.http.DefaultRenderStrategy'));
    }
}