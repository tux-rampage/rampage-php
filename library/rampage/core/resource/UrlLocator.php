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
     * @param UrlRepository $urlRepository
     */
    public function __construct(FileLocatorInterface $fileLocator, UrlRepository $urlRepository)
    {
        $this->fileLocator = $fileLocator;
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
     * Resolve relative path
     *
     * @param string $filename
     * @param string $scope
     * @param $theme
     */
    protected function resolve($filename, $scope, $theme)
    {
        if (isset($this->locations[$theme][$scope][$filename])) {
            return $this->locations[$theme][$scope][$filename];
        }

        $info = $this->getFileLocator()->publish($filename, $scope);

        if (!$info || !$info->isValid()) {
            throw new RuntimeException(sprintf('Failed to locate "%s::%s" in theme "%s"', $scope, $filename, $theme));
        }

        $this->locations[$theme][$scope][$filename] = $info;
        return $info;
    }

    /**
     * @see \rampage\core\resource\UrlLocatorInterface::getPublicFileInfo()
     */
    public function getPublicFileInfo($file, $scope = null)
    {
        if (!$scope && (strpos($file, '::') !== false)) {
            @list($scope, $file) = explode('::', $file, 2);
        }

        $theme = $this->getCurrentTheme();
        return $this->resolve($file, $scope, $theme);
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\core\resource\UrlLocatorInterface::getUrl()
     */
    public function getUrl($file, $scope = null)
    {
        $info = $this->getPublicFileInfo($file, $scope);
        return $this->getUrlModel($info->getUrlType())->getUrl($info->getRelativePath());
    }
}