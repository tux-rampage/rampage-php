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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\events;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventInterface;

/**
 * Shared events initializer
 */
class SharedEventsInitializer
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceLocator = null;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param Event $event
     */
    public function __invoke(EventInterface $event)
    {
        $serviceLocator = $this->getServiceLocator();
        $eventManager = $serviceLocator->get('SharedEventManager');
        $config = $serviceLocator->get('rampage\core\event\Config');

        if (!($config instanceof ConfigInterface) || !($eventManager instanceof SharedEventManager)) {
            return;
        }

        $appConfig = $serviceLocator->get('config');
        $eventsConfig = isset($appConfig['rampage']['events']['events'])? $appConfig['rampage']['events']['events'] : array();
        $configFiles = isset($appConfig['rampage']['events']['configfiles'])? $appConfig['rampage']['events']['configfiles'] : array();

        $config->setConfigArray($eventsConfig)
            ->setFiles($configFiles);

        $eventManager->setConfig($config);
    }
}
