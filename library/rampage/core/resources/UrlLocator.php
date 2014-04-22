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

use Zend\View\HelperPluginManager;

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
     * Cached url locations
     *
     * @var string
     */
    protected $locations = null;

    /**
     * @var PublishingStrategyInterface
     */
    protected $publishingStrategy = null;

    /**
     * @var HelperPluginManager
     */
    protected $helpers = null;

    /**
     * Construct
     *
     * @param FileLocatorInterface $fileLocator
     * @param UrlRepository $urlRepository
     */
    public function __construct(ThemeInterface $theme, PublishingStrategyInterface $strategy, HelperPluginManager $helpers)
    {
        $this->theme = $theme;
        $this->helpers = $helpers;
        $this->publishingStrategy = $strategy;
    }

    /**
     * @param PublishingStrategyInterface $strategy
     * @return self
     */
    public function setPublishingStrategy(PublishingStrategyInterface $strategy)
    {
        $this->publishingStrategy = $strategy;
        return $this;
    }

    /**
     * @param HelperPluginManager $helpers
     * @return self
     */
    public function setViewHelperManager(HelperPluginManager $helpers)
    {
        $this->helpers = $helpers;
        return $this;
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
    protected function resolve($filename, $scope)
    {
        $scopeIndex = ($scope === false)? '' : $scope;
        $theme = $this->getCurrentTheme();
        $url = false;

        if (isset($this->locations[$theme][$scopeIndex][$filename])) {
            return $this->locations[$theme][$scopeIndex][$filename];
        }

        if (!$this->publishingStrategy) {
            $url = $this->publishingStrategy->find($filename, $scope, $this->getTheme());
        }

        if ($url == false) {
            $urlHelper = $this->helpers->get('url');
            $url = $urlHelper('rampage.core.resources', array(
                'theme' => $theme,
                'scope' => ($scope? : '__theme__'),
                'file' => $filename
            ));
        }

        $this->locations[$theme][$scopeIndex][$filename] = $url;
        return $url;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\resource\UrlLocatorInterface::getUrl()
     */
    public function getUrl($file, $scope = null)
    {
        if (!$scope && ($scope !== false) && (strpos($file, '::') !== false)) {
            @list($scope, $file) = explode('::', $file, 2);
        }

        return $this->resolve($file, $scope);
    }
}
