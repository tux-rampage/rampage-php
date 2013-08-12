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

namespace rampage\core\view\renderer;

use rampage\core\view\TemplateInterface;
use rampage\core\view\Layout;
use rampage\core\view\HtmlCache;
use rampage\core\view\RenderableInterface;

use rampage\core\resources\ThemeInterface;
use rampage\core\exception\DependencyException;

use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;
use Zend\View\HelperPluginManager;

use ArrayObject;

/**
 * PHP File render strategy
 */
class PhpRenderer implements RendererInterface
{
    /**
     * current view
     *
     * @var ViewInterface
     */
    private $view = null;

    /**
     * View data
     *
     * @var \ArrayObject
     */
    private $data = null;

    /**
     * Template resolver
     *
     * @var \rampage\core\resources\Theme
     */
    private $templateResolver = null;

    /**
     * Cache instance
     *
     * @var \rampage\core\view\HtmlCache
     */
    protected $cache = null;

    /**
     * Helper plugin manager
     *
     * @var \Zend\View\HelperPluginManager
     */
    private $helpers = null;

    /**
     * Layout output
     *
     * @var unknown
     */
    protected $layoutOutputType = 'html';

    /**
     * Constructor
     */
    public function __construct(HelperPluginManager $pluginManager)
    {
        $this->helpers = $pluginManager;
        $this->data = new ArrayObject();
    }

    /**
     * Get helper plugin manager instance
     *
     * @return HelperPluginManager
     */
    public function getPluginManager()
    {
        if (null === $this->helpers) {
            throw new DependencyException('Missing plugin manager instance for php renderer');
        }

        return $this->helpers;
    }

    /**
     * Returns the plugin
     *
     * @param string $name
     * @param array $options
     * @return \Zend\View\Helper\AbstractHelper
     */
    public function plugin($name, array $options = null)
    {
        return $this->getPluginManager()->get($name, $options);
    }

    /**
     * @param ThemeInterface $locator
     * @return $this
     */
    public function setTemplateResolver(ThemeInterface $locator)
    {
        $this->templateResolver = $locator;
        return $this;
    }

    /**
     * Returns the template resolver
     *
     * @return \rampage\core\resources\ThemeInterface
     */
    protected function getTemplateResolver()
    {
        return $this->templateResolver;
    }

    /**
     * Resolver (ignored)
     * @param ResolverInterface $resolver
     */
    public function setResolver(ResolverInterface $resolver)
    {
        return $this;
    }

    /**
     * Get the Engine
     * @return \rampage\core\view\renderer\PhpRenderer
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * Returns the data object
     *
     * @return \ArrayObject
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param array|ArrayObject $values
     * @return \rampage\core\view\renderer\PhpRenderer
     */
    public function setData($values)
    {
        if (is_array($values)) {
            $this->getData()->exchangeArray($values);
            return $this;
        }

        if ($values instanceof ArrayObject) {
            $this->data = $values;
        }

        return $this;
    }

    /**
     * Set HTML cache
     *
     * @param HtmlCache $cache
     * @return \rampage\core\view\renderer\PhpRenderer
     */
    public function setCache(HtmlCache $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Fetch cache for the given view
     *
     * @param object $view
     * @return boolean|string
     */
    protected function fetchCache($view)
    {
        if (!$this->cache instanceof HtmlCache) {
            return false;
        }

        return $this->cache->fetch($this, $view);
    }

    /**
     * Save cache data
     *
     * @param object $view
     * @param string $html
     * @return boolean
     */
    protected function saveCache($view, &$html)
    {
        if (!$this->cache instanceof HtmlCache) {
            return false;
        }

        return $this->cache->store($this, $view, $html);
    }

    /**
     * Fetch view from template
     *
     * @param string $template
     */
    protected function fetchView(TemplateInterface $view)
    {
        $file = $view->getTemplate();
        if (!$file) {
            return '';
        }

        $file .= '.phtml';
        $path = $this->getTemplateResolver()->resolve('template', $file, null, true);

        if (($path === false) || !$path->isReadable()) {
            return '';
        }

        $wrapper = new PhtmlTemplate($this->getData(), $this, $view, $path);
        return $wrapper->render();
    }

    /**
     * Lyout output type
     *
     * @param string $type
     */
    public function setLayoutOutputType($type)
    {
        $this->layoutOutputType = $type;
        return $this;
    }

    /**
     * Render the given View
     *
     * @param \rampage\core\view\TemplateViewInterface $view
     * @return string
     */
    public function render($view, $values = null)
    {
        $output = '';

        if ($view instanceof Layout) {
            $this->setData($view->getData());

            foreach ($view->getOutputViews($this->layoutOutputType) as $childName) {
                $child = $view->getView($childName);

                if ($child instanceof RenderableInterface) {
                    $child->setViewRenderer($this);
                    $output .= $child->render();
                } else {
                    $output .= $this->render($child);
                }
            }

            return $output;
        }

        if (!$view instanceof TemplateInterface) {
            return $output;
        }

        if ($view instanceof RenderableInterface) {
            $view->setViewRenderer($this);
        }

        $cache = $this->fetchCache($view);
        if ($cache !== false) {
            return $cache;
        }

        $html = $this->fetchView($view);
        $this->saveCache($view, $html);

        return $html;
    }
}