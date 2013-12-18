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

namespace rampage\core\resources;

use rampage\core\url\UrlModelLocator;
use rampage\core\exception\RuntimeException;

/**
 * Theme auto publishing
 */
class UrlLocator implements UrlLocatorInterface
{
    /**
     * The file locator instance
     *
     * @var \rampage\core\resources\ThemeInterface
     */
    private $theme = null;

    /**
     * URL Model
     *
     * @var \rampage\core\url\UrlModelLocator
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
    public function __construct(ThemeInterface $theme, UrlModelLocator $urlRepository)
    {
        $this->theme = $theme;
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
     * @return \rampage\core\resources\ThemeInterface
     */
    protected function getTheme()
    {
        return $this->theme;
    }

    /**
     * Current theme
     *
     * @return null
     */
    protected function getCurrentTheme()
    {
        return $this->getTheme()->getCurrentTheme();
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
        $scopeIndex = ($scope === false)? '' : $scope;

        if (isset($this->locations[$theme][$scopeIndex][$filename])) {
            return $this->locations[$theme][$scopeIndex][$filename];
        }

        $info = $this->getTheme()->publish($filename, $scope);

        if (!$info || !$info->isValid()) {
            throw new RuntimeException(sprintf('Failed to locate "%s::%s" in theme "%s"', $scope, $filename, $theme));
        }

        $this->locations[$theme][$scopeIndex][$filename] = $info;
        return $info;
    }

    /**
     * @return PublicFileInfo
     */
    protected function getPublicFileInfo($file, $scope = null)
    {
        if (!$scope && ($scope !== false) && (strpos($file, '::') !== false)) {
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
