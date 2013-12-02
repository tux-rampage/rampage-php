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

namespace rampage\core\resources;

use SplFileInfo;
use rampage\core\PathManager;

/**
 * Theme implementation
 */
class Theme extends FileLocator implements ThemeInterface
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
    protected $current = '__default__';

    /**
     * Constructor
     *
     * @service rampage.resource.FileLocator $fallback force
     */
    public function __construct(PathManager $pathManager, FileLocatorInterface $fallback = null)
    {
        parent::__construct($pathManager);
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
     * @inheritdoc
     * @see \rampage\core\resource\FileLocatorInterface::publish()
     */
    public function publish($file, $scope = null)
    {
        if (!$scope && (strpos($file, '::') !== false)) {
            @list($scope, $file) = explode('::', $file, 2);
        }

        $relative = $this->findStaticPublicFile($file, $scope, array('static/theme', $this->getCurrentTheme()));
        if ($relative !== false) {
            return new PublicFileInfo($relative);
        }

        $parts = array('themes', $this->getCurrentTheme(), $scope, $file);
        $relative = implode('/', array_filter($parts));
        $source = $this->resolveThemeFile('public', $file, $scope, true);
        $target = new SplFileInfo($this->getPathManager()->get('media', $relative));

        if ($target->isFile() && (($source !== false) && ($source->getMTime() <= $target->getMTime()))) {
            return new PublicFileInfo($relative, 'media');
        }

        if (($source === false) || !$source->isFile() || !$source->isReadable()) {
            if ($this->fallback) {
                return $this->fallback->publish($file, $scope);
            }

            return false;
        }

        $dir = $target->getPathInfo();
        if (!$dir->isDir() && !@mkdir($dir->getPathname(), 0777, true)) {
            return false;
        }

        if (!@copy($source->getPathname(), $target->getPathname())) {
            return false;
        }

        return new PublicFileInfo($relative, 'media');
    }

    /**
     * Internal resolve theme file
     *
     * @param string $type
     * @param string $file
     * @param string $scope
     * @param string $asFileInfo
     */
    protected function resolveThemeFile($type, $file, $scope, $asFileInfo = false)
    {
        if (!isset($this->locations[$this->current])) {
            return false;
        }

        $themePath = ($scope)? $scope . '/' . ltrim($file, '/') : ltrim($file, '/');
        $path = parent::resolve($type, $themePath, $this->current, true);

        if (($path === false) || !$path->isFile()) {
            return false;
        }

        if (!$asFileInfo) {
            $path = $path->getPathname();
        }

        return $path;
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

        $result = $this->resolveThemeFile($type, $file, $scope, $asFileInfo);

        if (($result === false) && $this->fallback) {
            $result = $this->fallback->resolve($type, $file, $scope, $asFileInfo);
        }

        return $result;
    }
}
