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

namespace rampage\core\view;

use Serializable;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Stdlib\ResponseInterface;
use Zend\Stdlib\RequestInterface;

use rampage\core\xml\SimpleXmlElement;
use rampage\core\exception\InvalidArgumentException;
use rampage\core\data\ArrayExchangeInterface;

/**
 * Layout
 */
class Layout implements EventManagerAwareInterface, Serializable
{
    /**
     * Before load event name
     */
    const EVENT_LOAD_BEFORE = 'load.before';

    /**
     * After laod event name
     */
    const EVENT_LOAD_AFTER = 'load.after';

    /**
     * View elements
     *
     * @var array
     */
    private $views = array();

    /**
     * Layout Updates
     *
     * @var \rampage\core\view\LayoutUpdate
     */
    private $update = null;

    /**
     * Event manager
     *
     * @var \Zend\EventManager\EventManagerInterface
     */
    private $events = null;

    /**
     * Returns the view locator
     *
     * @var ViewLocator
     */
    private $locator = null;

    /**
     * Output elements
     *
     * @var array
     */
    private $output = array();

    /**
     * Ignored views during load
     *
     * @var string
     */
    protected $ignoredViews = array();

    /**
     * Response instance
     *
     * @var \Zend\Stdlib\ResponseInterface
     */
    private $response = null;

    /**
     * Request instance
     *
     * @var \Zend\Stdlib\RequestInterface
     */
    private $request = null;

    /**
     * Data
     *
     * @var \ArrayObject
     */
    private $data = null;

    /**
     * Construct
     */
    public function __construct(LayoutUpdate $update, ViewLocator $locator)
    {
        $this->setUpdate($update);
        $this->setViewLocator($locator);
    }

    /**
     * Data
     *
     * @return \ArrayObject
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Request instance
     *
     * @return \Zend\Stdlib\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Response instance
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * set response
     *
     * @param \Zend\Stdlib\ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }

	/**
     * Set request
     *
     * @param \Zend\Stdlib\RequestInterface $request
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        $data = array(
            'views' => $this->views,
            'output' => $this->output
        );

        return serialize($data);
    }

	/**
     * (non-PHPdoc)
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $layout = $this;

        $this->views = $data['views'];
        $this->output = $data['output'];

        array_walk($this->views, function($item) use ($layout) {
            if (!$item instanceof LayoutViewInterface) {
                continue;
            }

            $item->setLayout($layout);
        });

        return $this;
    }

	/**
     * Returns the view locator
     *
     * @param ViewLocator $locator
     */
    public function setViewLocator(ViewLocator $locator)
    {
        $this->locator = $locator;
    }

    /**
     * Returns the view locator
     *
     * @return \rampage\core\view\ViewLocator
     */
    public function getViewLocator()
    {
        return $this->locator;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\EventManager\EventManagerAwareInterface::setEventManager()
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->events = $eventManager;
        return $this;
    }


    /**
     * (non-PHPdoc)
     * @see \Zend\EventManager\EventsCapableInterface::getEventManager()
     */
    public function getEventManager()
    {
        return $this->events;
    }

	/**
     * Layout update
     *
     * @param \rampage\core\view\LayoutUpdate $update
     * @return \rampage\core\view\Layout
     */
    public function setUpdate(LayoutUpdate $update)
    {
        $this->update = $update;
        return $this;
    }

    /**
     * Layout update
     *
     * @return \rampage\core\view\LayoutUpdate
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     *  Prepare ignores
     *
     * @param SimpleXmlElement $xml
     */
    protected function prepareIgnores(SimpleXmlElement $xml)
    {
        foreach ($xml->xpath('.//remove') as $ignore) {
            $name = (string)$ignore['name'];
            if (!$name) {
                continue;
            }

            $this->ignoredViews[$name] = true;
        }

        return $this;
    }

    /**
     * Check if node can be processed
     *
     * @param SimpleXmlElement $node
     * @return boolean
     */
    protected function canProcessNode(SimpleXmlElement $node)
    {
        if (in_array($node->getName(), array())) {
            $name = (string)$node['name'];
            $ignore = (isset($this->ignoredViews[$name]))? $this->ignoredViews[$name] : false;

            return !$ignore;
        }

        return true;
    }

    /**
     * create a view from xml
     *
     * @param SimpleXmlElement $xml
     * @param LayoutViewInterface $parent
     */
    protected function createViewFromXml(SimpleXmlElement $xml, LayoutViewInterface $parent = null)
    {
        $class = (string)$xml['class'];
        $name = (string)$xml['name'];
        $data = array();

        if (!$class) {
            return false;
        }

        $view = $this->createView($class, $name, $data);
        if (!$view) {
            return false;
        }

        if (isset($xml->data) && ($view instanceof ArrayExchangeInterface)) {
            $data = $xml->data->toPhpValue('array');
            $view->populate($data);
        }

        if ($parent) {
            $alias = isset($xml['alias'])? (string)$xml['alias'] : null;
            $after = true;
            $sibling = null;

            if (isset($xml['before'])) {
                $sibling = ($xml['before'] == '-')? null : (string)$xml['before'];
                $after = false;
            } else if (isset($xml['after']) && ($xml['after'] != '-')) {
                $sibling = (string)$xml['after'];
            }

            $parent->addChild($view, $alias, $sibling, $after);
        }

        if (isset($xml['template']) && is_callable(array($view, 'setTemplate'))) {
            $view->setTemplate((string)$xml['template']);
        }

        // Output block
        if (isset($xml['output'])) {
            $output = array_filter(array_map('trim', explode(',', (string)$xml['output'])));

            foreach ($output as $type) {
                $this->addOutputView($type, $name);
            }
        }

        return $view;
    }

    /**
     * Create action
     *
     * @param LayoutViewInterface $view
     * @param SimpleXmlElement $xml
     */
    protected function createActionFromXml(LayoutViewInterface $view, SimpleXmlElement $xml)
    {
        $method = (string)$xml['method'];
        if (!$method || is_callable(array($view, $method))) {
            return $this;
        }

        $args = $xml->toPhpValue('array');
        call_user_func_array(array($view, $method), $args);

        return $this;
    }

    /**
     * Add data from xml
     *
     * @param LayoutViewInterface $view
     * @param SimpleXmlElement $xml
     * @return \rampage\core\view\Layout
     */
    protected function addDataFromXml(LayoutViewInterface $view, SimpleXmlElement $xml)
    {
        if (!$view instanceof ArrayExchangeInterface) {
            return $this;
        }

        $view->add($xml->toPhpValue('array'));
        return $this;
    }

    /**
     * Simple xml element
     *
     * @param SimpleXmlElement $xml
     * @param \rampage\core\view\LayoutViewInterface $parent
     */
    protected function createOutputFromXml(SimpleXmlElement $xml, $parent)
    {
        if (!$parent instanceof LayoutViewInterface) {
            return $this;
        }

        $output = array_filter(array_map('trim', explode(',', (string)$xml)));
        foreach ($output as $type) {
            $this->addOutputView($type, $output->getNameInLayout());
        }

        return $this;
    }

    /**
     * Create views
     *
     * @param SimpleXmlElement $xml
     * @param \rampage\core\view\LayoutViewInterface $parent
     * @return \rampage\core\view\Layout
     */
    protected function createFromXml(SimpleXmlElement $xml, $parent = null, $noData = false)
    {
        foreach ($xml->children() as $type => $child) {
            if (!$this->canProcessNode($child)) {
                continue;
            }

            switch ($type) {
                case 'view':
                    $view = $this->createViewFromXml($xml, $parent);
                    if ($view) {
                        $this->createFromXml($xml, $view, true);
                    }

                    break;

                case 'reference':
                    $parent = $this->getView((string)$child['name']);
                    if ($parent) {
                        $this->createFromXml($child, $parent);
                    }

                    break;

                case 'action':
                    if ($parent) {
                        $this->createActionFromXml($parent, $xml);
                    }

                    break;

                case 'data':
                    if (!$noData && $parent) {
                        $this->addDataFromXml($parent, $xml);
                    }

                    break;

                case 'remove':
                    $name = (string)$child['name'];
                    if ($name && $parent) {
                        $parent->removeChild($name);
                    }

                    break;

                case 'template':
                    if ($parent instanceof TemplateInterface) {
                        $parent->setTemplate((string)$child);
                    }

                    break;

                case 'output':
                    $this->createOutputFromXml($parent, $child);
                    break;
            }
        }

        return $this;
    }

    /**
     * Create a view instance
     *
     * @param string $class
     * @param string $name
     * @param array|\Traversable $data
     * @return boolean|\rampage\core\view\Layout
     */
    public function createView($class, &$name, $data = null)
    {
        $view = $this->getViewLocator()->get($class);
        if (!$view instanceof LayoutViewInterface) {
            return false;
        }

        if ($data) {
            $view->set($data);
        }

        if (!is_string($name) || !$name) {
            $name = uniqid('_anonymous_', true);
        }

        $this->setView($view, $name);
        return $this;
    }

    /**
     * Set a view instance
     *
     * @param LayoutViewInterface $view
     * @param string $name
     * @return \rampage\core\view\Layout
     */
    public function setView(LayoutViewInterface $view, $name)
    {
        if (!is_string($name) || ($name == '')) {
            throw new InvalidArgumentException('View name must be a string and it must not be empty');
        }

        $view->setNameInLayout($name);
        $view->setLayout($this);

        $this->views[$name] = $view;
        return $this;
    }

    /**
     * Returns the requeested view or false
     *
     * @param string $name
     * @return boolean|\rampage\core\view\ViewInterface
     */
    public function getView($name)
    {
        if (!isset($this->views[$name])) {
            return null;
        }

        return $this->views[$name];
    }

    /**
     * Add an output view
     *
     * @param string $type
     * @param string $name
     * @return \rampage\core\view\Layout
     */
    public function addOutputView($type, $name)
    {
        $this->output[$type][$name] = $name;
        return $this;
    }

    /**
     * Remove output view
     *
     * @param string $name
     */
    public function removeOutputView($type, $name)
    {
        unset($this->output[$type][$name]);
        return $this;
    }

    /**
     * Get output views
     *
     * @param string $type
     * @return array
     */
    public function getOutputViews($type)
    {
        if (!isset($this->output[$type])) {
            return array();
        }

        return $this->output[$type];
    }

    /**
     * Load layout
     *
     * @param string $name
     */
    public function load($name, $default = null)
    {
        $result = $this->getEventManager()->trigger(static::EVENT_LOAD_BEFORE, $this);
        $data = $result->last();

        if (is_string($data)) {
            $this->unserialize($data);
            return $this;
        }

        $handles = $this->getUpdate()->collectNodes();
        foreach ($handles as $xml) {
            $this->prepareIgnores($xml);
        }

        foreach ($handles as $xml) {
            $this->createFromXml($xml);
        }

        $this->getEventManager()->trigger(static::EVENT_LOAD_AFTER, $this);
        return $this;
    }
}