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

/**
 * Layout view interface
 */
interface LayoutViewInterface extends RenderableInterface, Serializable
{
    /**
     * Set the layout reference
     *
     * @param \rampage\core\view\Layout $layout
     */
    public function setLayout(Layout $layout);

    /**
     * Set the block name in layout
     *
     * @param string $name
     */
    public function setNameInLayout($name);

    /**
     * Get the block name in layout
     *
     * @return string
     */
    public function getNameInLayout();

    /**
     * Add a child block
     *
     * @param \rampage\core\view\LayoutViewInterface $view
     * @param string $name
     * @param string $sibling
     * @param string $after
     */
    public function addChild(LayoutViewInterface $view, $name, $sibling = null, $after = true);

    /**
     * Remove a child view
     *
     * @param string $name
     */
    public function removeChild($name);

    /**
     * Return all child elements
     *
     * @return array
     */
    public function getChildren();

    /**
     * Returns a specific child
     *
     * @param string $name
     * @return \rampage\core\view\LayoutViewInterface
     */
    public function getChild($name);

    /**
     * Render a spcific child
     *
     * @param string $name
     */
    public function renderChild($name);

    /**
     * Render all children
     *
     * @return string
     */
    public function renderChildren();
}