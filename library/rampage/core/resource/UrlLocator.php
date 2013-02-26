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

namespace rampage\core\resource;

use SplFileInfo;
use rampage\core\PathManager;
use rampage\core\model\url\Media as MediaUrl;
use rampage\core\exception\RuntimeException;

/**
 * Theme auto publishing
 */
class UrlLocator implements UrlLocatorInterface
{
    /**
     * The file locator instance
     *
     * @var \rampage\core\resource\FileLocatorInterface
     */
    private $fileLocator = null;

    /**
     * Path Manager
     *
     * @var \rampage\core\PathManager
     */
    private $pathManager = null;

    /**
     * URL Model
     *
     * @var \rampage\core\model\Url
     */
    private $urlModel = null;

    /**
     * Cached url locations
     *
     * @var string
     */
    protected $locations = null;

    /**
     * Construct
     *
     * @param FileLocatorInterface $fileLocator
     * @param PathManager $pathmanager
     */
    public function __construct(FileLocatorInterface $fileLocator, PathManager $pathManager, MediaUrl $model)
    {
        $this->fileLocator = $fileLocator;
        $this->pathManager = $pathManager;
    }

    /**
     * URL model
     *
     * @return \rampage\core\model\Url
     */
    protected function getUrlModel()
    {
        return $this->urlModel;
    }

	/**
     * Current file locator instance
     *
     * @return \rampage\core\resource\FileLocatorInterface
     */
    protected function getFileLocator()
    {
        return $this->fileLocator;
    }

    /**
     * Path manager instance
     *
     * @return \rampage\core\PathManager
     */
    protected function getPathManager()
    {
        return $this->pathManager;
    }

    /**
     * Current theme
     *
     * @return null
     */
    protected function getCurrentTheme()
    {
        if ($this->getFileLocator() instanceof Theme) {
            return $this->getFileLocator()->getCurrentTheme();
        }

        return '__default__';
    }

    /**
     * Publish the given file
     *
     * @param string $type
     * @param string $file
     * @param string $scope
     * @param SplFileInfo $target
     */
    protected function publish($file, $scope, SplFileInfo $target)
    {
        if ($file instanceof SplFileInfo) {
            $source = $file;
        } else {
            $source = $this->getFileLocator()->resolve('public', $file, $scope, true);
        }

        if (!$source || !$source->isReadable() || !$source->isFile()) {
            return false;
        }

        if (!mkdir($target->getPath(), null, true)) {
            return false;
        }

        return copy($source->getPathname(), $target->getPathname());
    }

    /**
     * Resolve relative path
     *
     * @param string $type
     * @param string $file
     * @param string $scope
     */
    protected function resolve($filename, $scope, $theme)
    {
        if (isset($this->locations[$theme][$scope][$file])) {
            return $this->locations[$theme][$scope][$file];
        }

        $segments = array('theme', $theme, $scope, $filename);
        $relative = implode('/', array_filter($segments));
        $file = new SplFileInfo($this->getPathManager()->get('public', $relative));

        if ($file->isFile() && $file->isReadable()) {
            $this->locations[$theme][$scope][$file] = $relative;
            return $relative;
        }

        $file = new SplFileInfo($this->getPathManager()->get('media', $relative));
        $relative = 'media/' . $relative;
        $source = $this->getFileLocator()->resolve('public', $filename, $scope, true);
        $this->locations[$theme][$scope][$file] = $relative;

        if ($file->isReadable() && $file->isFile()
          && (!$source || ($source->getMTime() <= $file->getMTime()))) {
            return $relative;
        }

        if (!$this->publish(($source)?: $file, $scope, $file)) {
            throw new RuntimeException(sprintf('Failed to locate "%s::%s" in theme "%s"', $scope, $file, $theme));
        }

        return $relative;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\resource\UrlLocatorInterface::getUrl()
     */
    public function getUrl($file, $scope = null)
    {
        $theme = $this->getCurrentTheme();
        if (!$scope && (strpos($file, '::') !== false)) {
            @list($scope, $file) = explode('::', $file, 2);
        }

        return $this->getUrlModel()->getUrl($this->resolve($file, $scope, $theme));
    }
}