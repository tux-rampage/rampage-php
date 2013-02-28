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

namespace rampage\core\resource;

use SplFileInfo;

/**
 * Theme implementation
 */
class Theme extends FileLocator
{
    /**
     * Fallback
     *
     * @var \rampage\core\resource\FileLocator
     */
    private $fallback = null;

    /**
     * Current theme
     *
     * @var string
     */
    protected $current = 'default';

    /**
     * Constructor
     *
     * @service rampage.resource.FileLocator $fallback force
     */
    public function __construct(FileLocatorInterface $fallback = null)
    {
        $this->setFallback($fallback);
    }

    /**
     * Set fallback
     *
     * @param FileLocatorInterface $fallback
     * @return \rampage\core\resource\Theme
     */
    public function setFallback(FileLocatorInterface $fallback = null)
    {
        $this->fallback = $fallback;
        return $this;
    }

    /**
     * Set the current theme
     *
     * @param string $name
     * @return \rampage\core\resource\Theme
     */
    public function setCurrentTheme($name)
    {
        $name = (string)$name;
        if ($name == '') {
            $name = 'default';
        }

        $this->current = $name;
        return $this;
    }

    /**
     * Current theme
     *
     * @return string
     */
    public function getCurrentTheme()
    {
        return $this->current;
    }

    /**
     * Get all themes
     *
     * @return array
     */
    public function getThemes()
    {
        $themes = array_keys($this->locations);
        if (!isset($this->locations['default'])) {
            array_unshift($themes, 'default');
        }

        return $themes;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\resource\FileLocatorInterface::resolve()
     */
    public function resolve($type, $file, $scope = null, $asFileInfo = false)
    {
        if (!$scope && (strpos($file, '::') !== false)) {
            list($scope, $file) = explode('::', $file, 2);
        }

        if (!isset($this->locations[$this->current]) && $this->fallback) {
            return $this->fallback->resolve($type, $file, $scope, $asFileInfo);
        }

        $themePath = $scope . '/' . ltrim($file, '/');
        $path = parent::resolve($this->current, $type, $themePath, false);

        if (file_exists($path) && $this->fallback) {
            return $this->fallback->resolve($scope, $type, $file, $asFileInfo);
        }

        if ($asFileInfo) {
            $path = new SplFileInfo($path);
        }

        return $path;
    }
}