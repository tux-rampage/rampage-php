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

namespace rampage\core\event;

use rampage\core\ModuleRegistry;
use rampage\core\PathManager;
use rampage\core\ObjectManagerInterface;
use rampage\core\modules\AggregatedXmlConfig;
use Zend\EventManager\SharedEventManagerInterface;

/**
 * Event config
 */
class Config extends AggregatedXmlConfig implements ConfigInterface
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Array config data
     *
     * @var array
     */
    protected $data = array();

    /**
     * @see \rampage\core\modules\AggregatedXmlConfig::__construct()
     */
    public function __construct(ModuleRegistry $registry, PathManager $pathManager, ObjectManagerInterface $objectManager)
    {
        parent::__construct($registry, $pathManager);
        $this->objectManager = $objectManager;
    }

    /**
     * @return \rampage\core\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

	/**
     * @see \rampage\core\modules\AggregatedXmlConfig::getGlobalFilename()
     */
    protected function getGlobalFilename()
    {
        return 'events.xml';
    }

	/**
     * @see \rampage\core\modules\AggregatedXmlConfig::getModuleFilename()
     */
    protected function getModuleFilename()
    {
        return 'etc/events.xml';
    }

    /**
     * Set the config array
     *
     * @param array $config
     * @return \rampage\core\event\Config
     */
    public function setConfigArray($config)
    {
        if (!isset($config['rampage']['events'])
          || (!is_array($config['rampage']['events'])
          && !($config['rampage']['events'] instanceof \ArrayAccess))) {
            return $this;
        }

        $this->data = $config['rampage']['events'];
        return $this;
    }

    /**
     * Add listeners from config array
     *
     * @param SharedEventManagerInterface $manager
     * @param unknown $id
     * @param unknown $name
     */
    protected function addListenersFromArray(SharedEventManagerInterface $manager, $id, $name)
    {
        if (!isset($this->data[$id][$name]) || (!is_array($this->data[$id][$name])
          && !($this->data[$id][$name] instanceof \Traversable))) {
            return $this;
        }

        $objectManager = $this->getObjectManager();

        foreach ($this->data[$id][$name] as $listener) {
            if (!$listener) {
                continue;
            }

            $priority = 1;

            if (is_array($listener) && isset($listener['listener'])) {
                $priority = (isset($listener['priority']))? (int)$listener['priority'] : 1;
                $listener = $listener['listener'];
            }

            if (is_string($listener)) {
                $listener = $objectManager->get($listener);
            }

            $manager->attach($id, $name, $listener, $priority);
        }

        return $this;
    }

    /**
     * Configure the event manager
     *
     * @see \rampage\core\event\ConfigInterface::configureEventManager()
     */
    public function configureEventManager(SharedEventManagerInterface $eventManager, $id, $eventName)
    {
        $quotedId = $this->xpathQuote($id);
        $quotedEvent = $this->xpathQuote($eventName);
        $objectManager = $this->getObjectManager();

        $this->addListenersFromArray($eventManager, $id, $eventName);

        $xpath = "./listener[@scope = $quotedId and @event = $quotedEvent and @class != '']";
        foreach ($this->getXml()->xpath($xpath) as $node) {
            $class = (string)$node['class'];
            $priority = (string)$node['priority'];

            if (!$objectManager->has($class)) {
                continue;
            }

            $params = array();
            if (isset($node->options)) {
                $params = $node->options->toPhpValue('array', $objectManager);
            }

            $listener = $objectManager->newInstance($class, $params);
            $eventManager->attach($id, $eventName, $listener, (int)$priority);
        }

        return $this;
    }
}