<?php
/**
 * This is part of rampage-php
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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\ui;

use Zend\View\Helper\AbstractHelper;
use Zend\Json\Json;

/**
 * Allows triggering toast messages
 *
 * This helper will allow triggering toast messages on HTML pages
 */
class ToastViewHelper extends AbstractHelper
{
    /**
     * @var ToastContainer
     */
    protected $container = null;

    /**
     * @param ToastContainer $container
     */
    public function __construct(ToastContainer $container = null)
    {
        $this->container = $container? : new ToastContainer();
    }

    /**
     * Add a toast message
     *
     * @param string $toast
     * @param int $displayTime
     * @return self
     */
    public function __invoke($toast = null, $displayTime = null, $class = null)
    {
        if ($toast !== null) {
            $this->toast($toast, $displayTime, $class);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * @param ToastContainer $container
     * @return self
     */
    public function setContainer(ToastContainer $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @param string $toast
     * @param int $displayTime
     * @return self
     */
    public function toast($toast, $displayTime = null, $class = null)
    {
        if (!$toast instanceof Toast) {
            $toast = new Toast($toast, $displayTime, $class);
        }

        $this->container->add($toast);
        return $this;
    }

    /**
     * Render the javascript code
     *
     * @return string
     */
    public function render()
    {
        $codeFormat = '$.toast(%s, %s);';
        $items = array();
        $js = '';

        foreach ($this->container as $toast) {
            $items[] = sprintf($codeFormat, Json::encode($toast->getMessage()), Json::encode($toast->getOptions()));
        }

        if (!empty($items)) {
            $js = '<script type="text/javascript">//<![CDATA['."\n"
                . 'jQuery(function($) {' . "\n"
                . implode("\n", $items) . "\n"
                . "});\n" . '//]]></script>';
        }

        return $js;
    }
}
