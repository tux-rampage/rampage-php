<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\view\helpers;

use rampage\core\exception;
use Zend\Json\Json;
use Zend\View\Helper\AbstractHelper;


class RequireJsHelper extends AbstractHelper
{
    /**
     * @var string
     */
    protected $requireJsUrl = null;

    /**
     * @var string
     */
    protected $baseUrl = null;

    /**
     * @var array
     */
    protected $modules = [];

    /**
     * @var array
     */
    protected $packages = [];

    /**
     * @var array
     */
    protected $bundles = [];

    /**
     * @param string $requireJsUrl
     * @return self
     */
    public function __invoke($requireJsUrl = null)
    {
        if ($requireJsUrl !== null) {
            $this->setRequireJsUrl($requireJsUrl);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            return '<!-- Failed to render require.js -->';
        }
    }

    /**
     * Set the base URL
     *
     * @see http://requirejs.org/docs/api.html#config-baseUrl
     * @param string $url
     * @param bool $replace Replace the base url if it was already set.
     * @return self
     */
    public function setBaseUrl($url, $replace = true)
    {
        if ($replace || !$this->baseUrl) {
            $this->baseUrl = $url;
        }

        return $this;
    }

    /**
     * Define the URL to require.js
     *
     * @param string $requireJsUrl
     * @return self
     */
    public function setRequireJsUrl($requireJsUrl)
    {
        $this->requireJsUrl = $requireJsUrl;
        return $this;
    }

    /**
     * Define a require.js module location
     *
     * @see http://requirejs.org/docs/api.html#config-paths
     * @param string $name
     * @param string $location
     * @param bool $replace Replace the module definition if it already exists
     */
    public function addModule($name, $location, $replace = false)
    {
        if ($name == '') {
            throw new exception\InvalidArgumentException('The require.js module name must not be empty');
        }

        if ($replace || !isset($this->modules[$name])) {
            $this->modules[$name] = (string)$location;
        }

        return $this;
    }

    /**
     * Add a package definition.
     *
     * If the package is already defined it will be replaced.
     *
     * @see http://requirejs.org/docs/api.html#packages
     * @param string $name
     * @param string $location
     * @param string $main
     */
    public function addPackage($name, $location = null, $main = null)
    {
        $name = (string)$name;

        if ($name == '') {
            throw new exception\InvalidArgumentException('The require.js package name must not be empty');
        }

        if (!$location && !$main) {
            $this->packages[$name] = $name;
            return $this;
        }

        $this->packages[$name] = [
            'name' => $name,
        ];

        if ($location) {
            $this->packages[$name]['location'] = (string)$location;
        }

        if ($main) {
            $this->packages[$name]['main'] = (string)$main;
        }

        return $this;
    }

    /**
     * Add a new bundle definition.
     *
     * If the bundle is already defined, it will be replaced.
     *
     * @see http://requirejs.org/docs/api.html#config-bundles
     * @param string $name
     * @param array $deps
     * @return self
     */
    public function addBundle($name, array $deps)
    {
        if ($name == '') {
            throw new exception\InvalidArgumentException('The require.js bundle name must not be empty');
        }

        $this->bundles[$name] = [];
        $this->addToBundle($name, $deps);

        return $this;
    }

    /**
     * Adde dependencies to an existing bundle
     *
     * @see http://requirejs.org/docs/api.html#config-bundles
     * @param string $name
     * @param array|string $deps
     * @return self
     */
    public function addToBundle($name, $deps)
    {
        if ($name == '') {
            throw new exception\InvalidArgumentException('The require.js bundle name must not be empty');
        }

        if (!is_array($deps) && !($deps instanceof \Traversable)) {
            $deps = [ $deps ];
        }

        if (!isset($this->bundles[$name])) {
            $this->bundles[$name] = [];
        }

        foreach ($deps as $item) {
            $item = (string)$item;

            if ($item != '') {
                $this->bundles[$name][] = $item;
            }
        }

        return $this;
    }


    /**
     * @param string $name
     * @return bool
     */
    public function hasPackage($name)
    {
        return isset($this->packages[$name]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasModule($name)
    {
        return isset($this->modules[$name]);
    }

    /**
     * @param string $location
     * @return string
     */
    protected function renderLocation($location)
    {
        if (substr($location, 0, 2) == './') {
            $location = substr($location, 2);
        } else if (!preg_match('~^https?://~i', $location)) {
            $location = $this->view->resourceUrl($location);
        }

        return (string)$location;
    }

    /**
     * @return string
     */
    public function render()
    {
        if (!$this->requireJsUrl) {
            $this->requireJsUrl = $this->view->resourceUrl('@rampage.core/js/require.js');
        }

        $html = '<script src="' . $this->view->escapeHtmlAttr((string)$this->requireJsUrl) . '" type="text/javascript"></script>';
        $config = [];
        $modules = [];
        $packages = [];
        $bundles = [];

        foreach ($this->modules as $module => $location) {
            $modules[$module] = $this->renderLocation($location);
        }

        foreach ($this->packages as $name => $package) {
            if (isset($package['location'])) {
                $package['location'] = $this->renderLocation($package['location']);
            }

            $packages[] = $package;
        }

        foreach ($this->bundles as $name => $deps) {
            if (empty($deps)) {
                continue;
            }

            $bundles[$name] = $deps;
        }

        if ($this->baseUrl) {
            $config['baseUrl'] = (string)$this->baseUrl;
        }

        $config['paths'] = empty($modules)? false : $modules;
        $config['packages'] = empty($packages)? false : $packages;
        $config['bundles'] = empty($bundles)? false : $bundles;

        $config = array_filter($config);

        if (!empty($config)) {
            $html .= '<script type="text/javascript">//<![CDATA[' . "\n"
                   . 'require.config(' . Json::encode($config) . ');' . "\n"
                   . '//]]></script>';
        }

        return $html;
    }
}
