<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\core;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Module resolver
 */
class ModuleManagerListenerAggregate implements ListenerAggregateInterface
{
    /**
     * Attached listeners
     *
     * @var array
     */
    protected $listeners = array();

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $eventConfigListener = new modules\EventConfigModuleListener($this->serviceLocator);

        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, $eventConfigListener);
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, array($this, 'resolve'), 100);

        return $this;
    }

	/**
     * {@inheritdoc}
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
     * @param ModuleEvent $event
     * @return object|false
     */
    public function resolve(ModuleEvent $event)
    {
        $name = $event->getModuleName();
        $class = str_replace('.', '\\', $name) . '\\Module';

        if (class_exists($class)) {
            return new $class();
        }

        return false;
    }
}
