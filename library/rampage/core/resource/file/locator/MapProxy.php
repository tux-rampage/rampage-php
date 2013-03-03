<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2013 Axel Helmert
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

namespace rampage\core\resource\file;

use rampage\core\PathManager;
use rampage\core\resource\FileLocatorInterface;
use rampage\core\resource\Theme;
use SplFileInfo;

/**
 * Mapping proxy for file locators
 */
class MapProxy implements FileLocatorInterface
{
    /**
     * Mapping table
     *
     * @var array
     */
    protected $map = null;

    /**
     * Modification flag
     *
     * @var bool
     */
    protected $isModified = false;

    /**
     * Map file
     *
     * @var string
     */
    protected $mapFile = 'resources/files.map.php';

    /**
     * The parent file locator to proxy
     *
     * @var FileLocatorInterface
     */
    private $parent = null;

    /**
     * Path manager
     *
     * @var PathManager
     */
    private $pathManager = null;

    /**
     * Construct
     *
     * @param FileLocatorInterface $parent
     * @param PathManager $pathManager
     */
    public function __construct(FileLocatorInterface $parent, PathManager $pathManager)
    {
        $this->parent = $parent;
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
     * Load mapping from file
     *
     * @return \rampage\core\resource\file\MapProxy
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
     * @return \rampage\core\resource\file\MapProxy
     */
    private function saveMap()
    {
        if (!$this->isModified) {
            return $this;
        }

        $file = new SplFileInfo($this->getPathManager()->get('maps', $this->mapFile));
        $dir = $file->getPathInfo();

        if (!$dir->isDir() && !@mkdir($dir->getPathname())) {
            return $this;
        }

        $content = '<?php return ' . var_export($this->map, true) . ';';
        @file_put_contents($file->getPathname(), $content);

        return $this;
    }

    /**
     * @return \rampage\core\resource\FileLocatorInterface
     */
    protected function getParent()
    {
        return $this->parent;
    }

    /**
     * @return \rampage\core\PathManager
     */
    protected function getPathManager()
    {
        return $this->pathManager;
    }

    /**
     * Current theme
     *
     * @return string
     */
    protected function getCurrentTheme()
    {
        $parent = $this->getParent();

        if ($parent instanceof Theme) {
            return $parent->getCurrentTheme();
        }

        return '__default__';
    }

    /**
     * Fetch a path from mapping
     *
     * @param string $map The map to fetch from
     * @param string $file
     * @param string $scope
     * @param string $type
     * @return boolean|string
     */
    protected function fetchFromMap($map, $file, $scope, $type = 'public')
    {
        $this->loadMap();
        $theme = $this->getCurrentTheme();

        if (isset($this->map[$map][$theme][$type][$scope][$file])) {
            return $this->map[$map][$theme][$type][$scope][$file];
        }

        return false;
    }

    /**
     * Add a location to the mapping
     *
     * @param string $location
     * @param string $map
     * @param string $file
     * @param string $scope
     * @param string $type
     * @return \rampage\core\resource\file\MapProxy
     */
    public function addToMap($location, $map, $file, $scope, $type = 'public')
    {
        $this->loadMap();
        $theme = $this->getCurrentTheme();

        $this->map[$map][$theme][$type][$scope][$file] = $location;
        $this->isModified = true;

        return $this;
    }

    /**
     * Prepare file and scope params
     *
     * @param string $file
     * @param string $scope
     * @return \rampage\core\resource\file\MapProxy
     */
    protected function prepareFileAndScope(&$file, &$scope)
    {
        if (!$scope && (strpos($file, '::') !== false)) {
            @list($scope, $file) = explode('::', $file, 2);
        }

        return $this;
    }

    /**
     * @see \rampage\core\resource\FileLocatorInterface::publish()
     */
    public function publish($file, $scope = null)
    {
        $this->prepareFileAndScope($file, $scope);
        $relative = $this->fetchFromMap('publish', $file, $scope);

        if ($relative !== null) {
            return $relative;
        }

        $relative = $this->getParent()->publish($file, $scope);
        $this->addToMap($relative, 'publish', $file, $scope);
        return $relative;
    }

    /**
     * @see \rampage\core\resource\FileLocatorInterface::resolve()
     */
    public function resolve($type, $file, $scope = null, $asFileInfo = false)
    {
        $this->prepareFileAndScope($file, $scope);
        $path = $this->fetchFromMap('resolve', $file, $scope, $type);

        if ($path === null) {
            $path = $this->getParent()->resolve($type, $file, $scope, false);
            $this->addToMap($path, 'resolve', $file, $scope, $type);
        }

        if ($path && $asFileInfo) {
            $path = new SplFileInfo($path);
        }

        return $path;
    }
}