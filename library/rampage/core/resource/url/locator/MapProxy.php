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

namespace rampage\core\resource\url\locator;

use rampage\core\PathManager;
use rampage\core\resource\UrlLocatorInterface;
use rampage\core\model\url\Repository as UrlRepository;
use SplFileInfo;

/**
 * caching proxy for url locators
 */
class MapProxy implements UrlLocatorInterface
{
    /**
     * Mapping file name
     */
    const URL_MAP_FILE = 'resources/urls.map.php';

    /**
     * Caching map
     *
     * @var array
     */
    private $map = null;

    /**
     * Path manager
     *
     * @var string
     */
    private $pathManager = null;

    /**
     * Url repository
     *
     * @var \rampage\core\model\url\Repository
     */
    private $urlRepository = null;

    /**
     * Parent url locator which will be proxied
     *
     * @var UrlLocatorInterface
     */
    private $parent = null;

    /**
     * Map file
     *
     * @var string
     */
    protected $mapFile = self::URL_MAP_FILE;

    /**
     * Check if map is modified
     *
     * @var bool
     */
    protected $isModified = false;

    /**
     * Construct
     *
     * @param FileLocatorInterface $fileLocator
     * @param PathManager $pathmanager
     */
    public function __construct(UrlLocatorInterface $parent, PathManager $pathManager, UrlRepository $urlRepository)
    {
        $this->parent = $parent;
        $this->urlRepository = $urlRepository;
        $this->pathManager = $pathManager;
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        $this->saveMap();
    }

    /**
     * Path manager
     *
     * @return \rampage\core\PathManager
     */
    protected function getPathManager()
    {
        return $this->pathManager;
    }

	/**
     * Url model
     *
     * @return \rampage\core\model\url\Media
     */
    protected function getUrlModel($type)
    {
        return $this->urlRepository->getUrlModel($type);
    }

    /**
     * Parent locator
     *
     * @return \rampage\core\resource\UrlLocatorInterface
     */
    protected function getParent()
    {
        return $this->parent;
    }

    /**
     * Current theme
     *
     * @return string
     */
    public function getCurrentTheme()
    {
        $parent = $this->getParent();
        if (is_callable(array($this->getParent(), 'getCurrentTheme'))) {
            return $parent->getCurrentTheme();
        }

        return '__default__';
    }

    /**
     * Load map from file
     *
     * @return \rampage\core\resource\url\locator\MapProxy
     */
    private function loadMap()
    {
        if (is_array($this->map)) {
            return $this;
        }

        $file = new SplFileInfo($this->getPathManager()->get('maps', $this->mapFile));
        if ($file->isFile() && $file->isReadable()) {
            $this->map = include $file->getPathname();
        }

        if (!is_array($this->map)) {
            $this->map = array();
        }

        return $this;
    }

    /**
     * Save mapping to file
     *
     * @return \rampage\core\resource\url\locator\MapProxy
     */
    private function saveMap()
    {
        if (!$this->isModified) {
            return $this;
        }

        $content = '<?php return ' . var_export($this->map, true) . ';';
        $file = new SplFileInfo($this->getPathManager()->get('maps', $this->mapFile));
        $dir = $file->getPathInfo();

        if (!$dir->isDir() && !@mkdir($dir->getPathname(), 0777, true)) {
            return $this;
        }

        if (@file_put_contents($file->getPathname(), $content) !== false) {
            $this->isModified = false;
        }

        return $this;
    }

    /**
     * Fetch from map
     *
     * @param string $file
     * @param string $scope
     */
    protected function fetchFromMap($file, $scope, $theme)
    {
        $this->loadMap();
        if (!isset($this->map[$theme][$scope][$file])) {
            return false;
        }

        return $this->map[$theme][$scope][$file];
    }

    /**
     * Add an item to the map
     *
     * @param string $file
     * @param string $scope
     * @param string $theme
     * @param string $info
     * @return \rampage\core\resource\url\locator\MapProxy
     */
    protected function addToMap($file, $scope, $theme, $info)
    {
        $this->loadMap();
        $this->map[$theme][$scope][$file] = $info;
        $this->isModified = true;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\resource\UrlLocatorInterface::getRelativePath()
     */
    public function getRelativePath($file, $scope = null, &$urlType = null)
    {
        if (strpos($file, '::') !== false) {
            list($scope, $file) = explode('::', $file, 2);
        }

        $theme = $theme = (string)$this->getCurrentTheme();
        $scope = (string)$scope;

        $result = $this->fetchFromMap($file, $scope, $theme);
        if ($result) {
            list($relative, $urlType) = $result;
            return $relative;
        }

        $result = $this->getParent()->getRelativePath($file, $scope, $urlType);
        $this->addToMap($file, $scope, $theme, array((string)$result, (string)$urlType));

        return $result;
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