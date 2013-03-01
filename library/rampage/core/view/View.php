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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\view;

use rampage\core\data\Object;
use rampage\core\exception\RuntimeException;
use Zend\View\Renderer\RendererInterface;

/**
 * Default view implementation
 */
class View extends Object implements LayoutViewInterface
{
    /**
     * Child elements
     *
     * @var array
     */
    private $children = array();

    /**
     * Serialized child instances
     *
     * @var array
     */
    private $_serializedChildren = array();

    /**
     * Name in layout
     *
     * @var string
     */
    private $nameInLayout = null;

    /**
     * Current layout instance
     *
     * @var \rampage\core\view\Layout
     */
    private $layout = null;

    /**
     * Renderer
     *
     * @var \Zend\View\Renderer\RendererInterface
     */
    private $renderer = null;

    /**
     * Check if data exists in layout
     *
     * @param string $name
     * @return boolean
     */
    protected function hasLayoutData($name)
    {
        return $this->getLayout()->getData()->offsetExists($name);
    }

    /**
     * Fetch layout data
     *
     * @param string $name
     * @param string $default
     */
    protected function fetchLayoutData($name, $default = null)
    {
        if (!$this->hasLayoutData($name)) {
            return $default;
        }

        return $this->getLayout()->getData()->offsetGet($name);
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\view\ViewInteface::addChild()
     */
    public function addChild(LayoutViewInterface $view, $name, $sibling = null, $after = true)
    {
        if (isset($this->children[$name])) {
            unset($this->children[$name]);
        }

        $offset = -1;
        $lastOffset = (count($this->children) - 1);

        if ($sibling && ($sibling != '-') && isset($this->children[$sibling])) {
            $offset = array_search((string)$sibling, array_keys($this->children));

            if ($after) {
                $offset++;
            }
        }

        if (($offset < 0) || (($offset == 0) && !$after) || (($offset >= $lastOffset) && $after)) {
            if ($after) {
                $this->children[$name] = $view;
                return $this;
            }

            $this->children = array($name => $view) + $this->children;
            return $this;
        }

        $children = array_slice($this->children, 0, $offset, true)
                  + array($name => $view)
                  + array_slice($this->children, $offset, null, true);

        $this->children = $children;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\view\LayoutViewInterface::removeChild()
     */
    public function removeChild($name)
    {
        unset($this->children[$name]);
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\core\view\ViewInteface::getChildren()
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Returns a child
     *
     * @param string $name
     * @return \rampage\core\view\LayoutViewInterface
     */
    public function getChild($name)
    {
        if (!isset($this->children[$name])) {
            return null;
        }

        return $this->children[$name];
    }

    /**
     * Render a child
     *
     * @param string $name
     * @return string
     */
    public function renderChild($name)
    {
        $child = $this->getChild($name);
        if (!$child) {
            return '';
        }

        $child->setViewRenderer($this->getViewRenderer());
        return $child->render();
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\view\ViewInteface::getNameInLayout()
     */
    public function getNameInLayout()
    {
        return $this->nameInLayout;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\view\ViewInteface::setLayout()
     */
    public function setLayout(Layout $layout)
    {
        $this->layout = $layout;

        if (!$this->_serializedChildren) {
            return $this;
        }

        $map = function($name) use ($layout) {
            return $layout->getView($name);
        };

        $this->children = array_filter(array_map($map, $this->_serializedChildren));
        $this->_serializedChildren = null;

        return $this;
    }

    /**
     * Returns the current layout instance
     *
     * @return \rampage\core\view\Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\view\ViewInteface::setNameInLayout()
     */
    public function setNameInLayout($name)
    {
        $this->nameInLayout = $name;
        return $this;
    }

    /**
     * Additional serialize data
     *
     * @return string
     */
    protected function getSerializeData()
    {
        return array();
    }

    /**
     * Apply unserialize data
     *
     * @param array $data
     */
    protected function applyUnserializeData(array $data)
    {
    }

    /**
     * (non-PHPdoc)
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        $data = array(
            'children' => array(),
            'name_in_layout' => $this->nameInLayout,
            'template' => $this->template
        );

        foreach ($this->children as $name => $child) {
            $data['children'][$name] = $child->getNameInLayout();
        }

        $data += $this->getSerializeData();
        return serialize($data);
    }

    /**
     * (non-PHPdoc)
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $required = array('children', 'template', 'name_in_layout');
        if (!is_array($data)) {
            throw new RuntimeException('Failed to deserialize view');
        }

        foreach ($required as $key) {
            if (!array_key_exists($data, $key)) {
                throw new RuntimeException('Failed to deserialize view');
            }
        }


        $this->template = $data['template'];
        $this->_serializedChildren = (is_array($data['children']))? $data['children'] : array();
        $this->nameInLayout = $data['name_in_layout'];

        $this->applyUnserializeData($data);
        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\core\view\RenderableInterface::render()
     */
    public function render()
    {
        return $this->getViewRenderer()->render($this);
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\view\LayoutViewInterface::renderChildren()
     */
    public function renderChildren()
    {
        $output = '';

        foreach ($this->getChildren() as $child) {
            if (!$child instanceof RenderableInterface) {
                continue;
            }

            $child->setViewRenderer($this->getViewRenderer());
            $output .= $child->render();
        }

        return $output;
    }

	/**
     * Returns the view renderer
     *
     * @return \Zend\View\Renderer\RendererInterface
     */
    public function getViewRenderer()
    {
        return $this->renderer;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\core\view\RenderableInterface::setViewRenderer()
     */
    public function setViewRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }
}