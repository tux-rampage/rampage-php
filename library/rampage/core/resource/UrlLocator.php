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
use rampage\core\model\url\Repository as UrlRepository;
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
     * @var \rampage\core\model\url\Repository
     */
    private $urlRepository = null;

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
    public function __construct(FileLocatorInterface $fileLocator, PathManager $pathManager, UrlRepository $urlRepository)
    {
        $this->fileLocator = $fileLocator;
        $this->pathManager = $pathManager;
        $this->urlRepository = $urlRepository;
    }

    /**
     * URL model
     *
     * @return \rampage\core\model\url\Repository
     */
    protected function getUrlModel($type)
    {
        return $this->urlRepository->getUrlModel($type);
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
    public function getCurrentTheme()
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

        if (($source === false) || !$source->isReadable() || !$source->isFile()) {
            return false;
        }

        $dir = $target->getPathInfo();
        if (!$dir->isDir() && !@mkdir($dir->getPathname(), 0777, true)) {
            $path = $dir->getPathname();
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
    protected function resolve($filename, $scope, $theme, &$urlType)
    {
        if (isset($this->locations[$theme][$scope][$filename])) {
            list($relative, $urlType) = $this->locations[$theme][$scope][$filename];
            return $relative;
        }

        $segments = array('theme', $theme, $scope, $filename);
        $relative = implode('/', array_filter($segments));
        $urlType = null;

        $file = new SplFileInfo($this->getPathManager()->get('public', $relative));
        if ($file->isFile() && $file->isReadable()) {
            $this->locations[$theme][$scope][$filename] = array($relative, $urlType);
            return $relative;
        }

        $urlType = 'media';
        $file = new SplFileInfo($this->getPathManager()->get('media', $relative));
        $source = $this->getFileLocator()->resolve('public', $filename, $scope, true);

        $this->locations[$theme][$scope][$filename] = array($relative, $urlType);

        if ($file->isReadable() && $file->isFile()
          && (($source === false) || ($source->getMTime() <= $file->getMTime()))) {
            return $relative;
        }

        if (!$this->publish(($source)?: $file, $scope, $file)) {
            throw new RuntimeException(sprintf('Failed to locate "%s::%s" in theme "%s"', $scope, $file, $theme));
        }

        return $relative;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\resource\UrlLocatorInterface::getRelativePath()
     */
    public function getRelativePath($file, $scope = null, &$urlType = null)
    {
        if (!$scope && (strpos($file, '::') !== false)) {
            @list($scope, $file) = explode('::', $file, 2);
        }

        $theme = $this->getCurrentTheme();
        return $this->resolve($file, $scope, $theme, $urlType);
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\core\resource\UrlLocatorInterface::getUrl()
     */
    public function getUrl($file, $scope = null)
    {
        $urlType = null;
        $path = $this->getRelativePath($file, $scope, $urlType);

        return $this->getUrlModel($urlType)->getUrl($path);
    }
}