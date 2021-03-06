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

use rampage\core\PathManager;
use SplFileInfo;
use InvalidArgumentException;

/**
 * Theme implementation
 */
class Theme extends FileLocator implements ThemeInterface
{
    const DEFAULT_THEME = '__default__';

    /**
     * Fallback
     *
     * @var FileLocatorInterface
     */
    private $fallback = null;

    /**
     * Current theme
     *
     * @var string
     */
    protected $current = self::DEFAULT_THEME;

    /**
     * @var DesignConfig
     */
    protected $designConfig = null;

    /**
     * @var string[]
     */
    protected $fallbackThemes = null;

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
     * @param DesignConfig $config
     * @return self
     */
    public function setDesignConfig(DesignConfig $config)
    {
        $this->designConfig = $config;
        return $this;
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
            $name = '__default__';
        }

        $this->current = $name;
        $this->fallbackThemes = null;

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
     * @return string[]
     */
    public function getFallbackThemes()
    {
        if ($this->fallbackThemes !== null) {
            return $this->fallbackThemes;
        }

        if (!$this->designConfig instanceof DesignConfig) {
            return array();
        }

        $this->fallbackThemes = $this->designConfig->getFallbackThemes($this->getCurrentTheme());
        return $this->fallbackThemes;
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
     * @param string $type
     * @param string $theme
     * @param string $path
     * @return SplFileInfo|false
     */
    private function findThemeFile($type, $theme, $path)
    {
        $path = parent::resolve($type, $path, $theme, true);
        if (($path !== false) && !$path->isFile() && !$path->isDir()) {
            return false;
        }

        return $path;
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
        $themePath = ($scope)? $scope . '/' . ltrim($file, '/') : ltrim($file, '/');
        $path = false;
        $stack = $this->getFallbackThemes();

        array_unshift($stack, $this->getCurrentTheme());

        while (($path === false) && ($theme = array_shift($stack))) {
            $path = $this->findThemeFile($type, $theme, $themePath);
        }

        if (!$asFileInfo && ($path !== false)) {
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
        $assetPath = new AssetPath($file, $scope);
        $result = $this->resolveThemeFile($type, $assetPath->getPath(), $assetPath->getScope(), $asFileInfo);

        if (($result === false) && $this->fallback) {
            $result = $this->fallback->resolve($type, $assetPath, null, $asFileInfo);
        }

        return $result;
    }
}
