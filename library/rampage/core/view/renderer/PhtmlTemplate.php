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

namespace rampage\core\view\renderer;

use rampage\core\data\Object;
use ArrayObject;

/**
 * Render wrapper
 *
 * @method string url() url($name = null, array $params = array(), $options = array(), $reuseMatchedParams = false)
 * @method string resourceUrl() resourceUrl(string $resource)
 * @method string translate() translate(string $message)
 * @method string escapeHtml() escapeHtml(string $str)
 */
class PhtmlTemplate extends Object
{
    /**
     * View instance
     *
     * @var object
     */
    private $view = null;

    /**
     * Renderer
     *
     * @var PhpRenderer
     */
    private $renderer = null;

    /**
     * Plugin cache
     *
     * @var array
     */
    private $pluginCache = array();

    /**
     * Template file
     *
     * @var string
     */
    protected $template = null;

    /**
     * Construct
     *
     * @param \ArrayObject $data
     * @param PhpRenderer $renderer
     * @param object $view
     * @param string template
     */
    public function __construct(ArrayObject $data, PhpRenderer $renderer, $view, $template)
    {
        $this->view = $view;
        $this->data = $data;
        $this->renderer = $renderer;
        $this->template = $template;
    }

    /**
     * Get a plug in
     *
     * @param string $name
     * @return \Zend\View\Helper\HelperInterface
     */
    public function plugin($name, array $options = null)
    {
        return $this->renderer->plugin($name, $options);
    }

    /**
     * Returns the view instance
     *
     * @return \rampage\core\view\View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Render this template
     *
     * @return string
     */
    public function render()
    {
        ob_start();
        include $this->template;

        return ob_get_clean();
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\data\Object::get()
     */
    public function get($key, $default = null)
    {
        return parent::get($key, $default);
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\data\Object::has()
     */
    public function has($field)
    {
        return parent::has($field);
    }

    /**
     * Translate with arguments sprintf like
     *
     * @param string $message
     * @param ...
     * @return string
     */
    public function __($message)
    {
        return $this->__call('translateArgs', func_get_args());
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\core\data\Object::__call()
     */
    public function __call($method, $args)
    {
        $viewDelegate = array($this->getView(), $method);
        if (method_exists($this->getView(), $method) && is_callable($viewDelegate)) {
            return call_user_func_array($viewDelegate, $args);
        }

        if (in_array(substr($method, 0, 3), array('get', 'set', 'has')) || (substr($method, 0, 5) == 'unset')) {
            return parent::__call($method, $args);
        }

        if (!isset($this->pluginCache[$method])) {
            $this->pluginCache[$method] = $this->plugin($method, $args);
        }

        $helper = $this->pluginCache[$method];
        if (is_callable($helper)) {
            return call_user_func_array($helper, $args);
        }

        return $helper;
    }

    /**
     * Render a child view
     *
     * @param string $name The name of the child element to render
     * @return mixed
     */
    public function renderChild($name)
    {
        return $this->getView()->renderChild($name);
    }

    /**
     * Render all child elements
     *
     * @return string
     */
    public function renderChildren()
    {
        return $this->getView()->renderChildren();
    }
}