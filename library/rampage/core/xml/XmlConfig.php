<?php
/**
 * This is part of @application_name@
 * Copyright (c) 2010 Axel Helmert
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
 * @copyright Copyright (c) 2010 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\xml;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManager;

/**
 * Default XML config
 */
class XmlConfig implements EventManagerAwareInterface
{
    /**
     * Init merge rules event
     *
     * @var string
     */
    const EVENT_INIT_MERGERULES = 'initMergeRules';

    /**
     * Pre init
     */
    const EVENT_INIT_BEFORE = 'init.before';

    /**
     * Post init
     */
    const EVENT_INIT_AFTER = 'init.after';

    /**
     * xml data
     *
     * @var \rampage\core\xml\SimpleXmlElement
     */
    private $xml = null;

    /**
     * Init flag
     *
     * @var bool
     */
    protected $initialized = false;

    /**
     * Appliable merge rule
     *
     * @var \rampage\core\xml\MergeRuleInterface
     */
    private $mergeRules = null;

    /**
     * Event Manager
     *
     * @var \Zend\EventManager\EventManagerInterface
     */
    private $eventManager = null;

    /**
     * Node class to use
     *
     * @var string
     */
    protected $nodeClass = 'rampage\core\xml\SimpleXmlElement';

    /**
     * Filename
     *
     * @var string
     */
    protected $file = null;

    /**
     * Constructor
     */
    public function __construct($file = null)
    {
        $this->file = $file;
    }

    /**
     * Quote string for xpath
     *
     * @param string $string
     * @return string
     */
    protected function xpathQuote($string)
    {
        return SimpleXmlElement::quoteXpathValue($string);
    }

    /**
     * Internal init
     *
     * @return \rampage\core\xml\Config
     */
    protected function loadXml()
    {
        if (!$this->file || !$this->loadFile($this->file)) {
            $this->setXml('<config></config>');
        }

        return $this;
    }

    /**
     * Initialize config
     */
    public function init()
    {
        if ($this->initialized) {
            return $this;
        }

        $result = $this->getEventManager()->trigger(self::EVENT_INIT_BEFORE, $this);
        $xml = $result->last();

        // Init before may short circuit init process (i.e. Chaching)
        if (is_string($xml) || ($xml instanceof SimpleXmlElement) || (is_object($xml) && method_exists($xml, '__toString'))) {
            $this->initialized = true;
            return $this->setXml($xml);
        }

        $this->loadXml();
        $this->getEventManager()->trigger(self::EVENT_INIT_AFTER, $this);

        $this->initialized = true;
        return $this;
    }

    /**
     * Event Manager
     *
     * @return \Zend\EventManager\EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->eventManager) {
            $this->eventManager = new EventManager();
        }

        return $this->eventManager;
    }

    /**
     * (non-PHPdoc)
     * @see Zend\EventManager.EventManagerAwareInterface::setEventManager()
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $class = get_class($this);
        $eventManager->setIdentifiers(array(
            strtr($class, '\\', '.'),
            $class
        ));

        $this->eventManager = $eventManager;
        return $this;
    }

    /**
     * Returns the default merge rule chain
     *
     * @return \rampage\core\xml\mergerule\ChainedRule
     */
    protected function getDefaultMergeRulechain()
    {
        return new mergerule\ChainedRule();
    }

    /**
     * Initialize merge rules
     */
    private function initMergeRules()
    {
        $rules = $this->getDefaultMergeRulechain();
        $this->setMergeRules($rules);
        $this->getEventManager()->trigger(self::EVENT_INIT_MERGERULES, $this, array('rules' => $rules));

        return $this;
    }

	/**
	 * Get xml data
	 *
     * @return \rampage\core\xml\SimpleXmlElement
     */
    public function getXml()
    {
        if (!$this->xml instanceof SimpleXmlElement) {
            $this->init();
        }

        return $this->xml;
    }

	/**
	 * Set xml
	 *
     * @param \rampage\core\xml\SimpleXmlElement|string $xml
     */
    public function setXml($xml)
    {
        $class = $this->nodeClass;

        if (!$xml instanceof $class) {
            $xml = new $class((string)$xml);
        }

        $this->xml = $xml;
        return $this;
    }

	/**
	 * Returns the current merge rules
	 *
     * @return \rampage\core\xml\mergerule\ChainedRule
     */
    public function getMergeRules()
    {
        if (!$this->mergeRules) {
            $this->initMergeRules();
        }

        return $this->mergeRules;
    }

	/**
	 * set merge rules
	 *
     * @param \rampage\core\xml\mergerule\ChainedRule $rules
     */
    public function setMergeRules(mergerule\ChainedRule $rules = null)
    {
        $this->mergeRules = $rules;
        return $this;
    }

    /**
     * Load xml from file
     *
     * @param string $file
     * @return bool
     */
    public function loadFile($file)
    {
        $xml = file_get_contents($file);
        if (!$xml) {
            return false;
        }

        try {
            $this->setXml($xml);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Merge configs
     *
     * @param AbstractConfig $config
     * @param bool $replace
     */
    public function merge(XmlConfig $config, $replace = true)
    {
        $this->getXml()->merge($config->getXml(), $replace, $this->getMergeRules());
        return $this;
    }

    /**
     * Get a node
     *
     * @param string $xpath
     * @return \rampage\core\xml\SimpleXmlElement|null
     */
    public function getNode($xpath = null)
    {
        if ($xpath === null) {
            return $this->getXml();
        }

        $result = $this->getXml()->xpath($xpath)->current();
        if ($result === false) {
            $result = null;
        }

        return $result;
    }
}