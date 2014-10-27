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

use rampage\core\exception;
use rampage\core\url\UrlModelInterface;

use Zend\View\HelperPluginManager;
use Zend\Mvc\Router\RouteInterface;


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
     * @var RouteInterface
     */
    protected $router = null;

    /**
     * @var UrlModelInterface
     */
    protected $urlModel = null;

    /**
     * Construct
     *
     * @param FileLocatorInterface $fileLocator
     * @param UrlRepository $urlRepository
     */
    public function __construct(ThemeInterface $theme, PublishingStrategyInterface $strategy)
    {
        $this->theme = $theme;
        $this->publishingStrategy = $strategy;
    }

    /**
     * @param RouteInterface $router
     * @return self
     */
    public function setRouter(RouteInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @param UrlModelInterface $urlModel
     */
    public function setUrlModel(UrlModelInterface $urlModel)
    {
        $this->urlModel = $urlModel;
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

        if ($this->publishingStrategy) {
            $url = $this->publishingStrategy->find($filename, $scope, $this->getTheme());
        }

        if ($url === false) {
            if (!$this->router) {
                throw new exception\DependencyException('Missing router instance to build dynamic resource url');
            }

            $routeOptions = array(
                'name' => 'rampage.core.resources',
            );

            $routeParams = array(
                'theme' => $theme,
                'scope' => ($scope? : '__theme__'),
                'file' => $filename
            );

            $url = $this->router->assemble($routeParams, $routeOptions);

            if ($this->urlModel) {
                $url = $this->urlModel->getUrl($url);
            }
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
        $asset = new AssetPath($file, $scope);
        return $this->resolve($asset->getPath(), $asset->getScope());
    }
}
