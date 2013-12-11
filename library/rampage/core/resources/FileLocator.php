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
use rampage\core\exception\InvalidArgumentException;
use rampage\core\PathManager;

/**
 * File locator
 */
class FileLocator implements FileLocatorInterface
{
    /**
     * File locations
     *
     * @var array
     */
    protected $locations = array();

    /**
     * Available types
     *
     * @var array
     */
    protected $types = array(
        'public',
        'template',
        'layout',
        'db',
    );

    /**
     * Pathmanager
     *
     * @var \rampage\core\PathManager
     */
    private $pathManager = null;

    /**
     * Construct
     *
     * @param PathManager $pathManager
     */
    public function __construct(PathManager $pathManager)
    {
        $this->pathManager = $pathManager;
    }

    /**
     * @return \rampage\core\PathManager
     */
    protected function getPathManager()
    {
        return $this->pathManager;
    }

	/**
     * Add a location
     *
     * @param string $scope
     * @param string|array $path
     */
    public function addLocation($scope, $path)
    {
        if ($path instanceof SplFileInfo) {
            $path = array('base' => $path->getPathname());
        } else if (is_string($path)) {
            if ($path == '') {
                throw new InvalidArgumentException('Invalid resource path: path must not be empty.');
            }

            $path = array('base' => $path);
        } else if (!is_array($path) && !($path instanceof \ArrayAccess)) {
            throw new InvalidArgumentException('Invalid path');
        }

        foreach ($this->types as $type) {
            if (isset($path[$type])) {
                $this->locations[$scope][$type] = $path[$type];

                if ($type != 'base') {
                    unset($path[$type]);
                }

                continue;
            }

            if (!isset($path['base'])) {
                if (!isset($this->locations[$scope]['base'])) {
                    throw new InvalidArgumentException('Invalid resource path: No base dir specified');
                }

                $path['base'] = $this->locations[$scope]['base'];
            }

            $this->locations[$scope][$type] = $path['base'] . '/' . $type;
        }

        unset($path['base']);
        foreach ($path as $type => $extraPath) {
            $this->locations[$scope][$type] = $extraPath;
        }

        return $this;
    }

    /**
     * @deprecated
     * @param string $file
     * @param string $scope
     * @param string|array $segements
     */
    protected function findStaticPublicFile($file, $scope, $segments)
    {
        // check static module file
        if (!is_array($segments)) {
            $segments = array($segments);
        }

        $segments[] = $scope;
        $segments[] = $file;

        $relative = implode('/', array_filter($segments));
        $file = new SplFileInfo($this->getPathManager()->get('public', $relative));

        if (!$file->isFile()) {
            return false;
        }

        return $relative;
    }

    /**
     * @see \rampage\core\resource\FileLocatorInterface::publish()
     */
    public function publish($file, $scope = null)
    {
        if (strpos($file, '::') !== false) {
            @list($scope, $file) = explode('::', $file, 2);
        }

        $relative = $this->findStaticPublicFile($file, $scope, 'static/resource');
        if ($relative !== false) {
            return new PublicFileInfo($relative);
        }

        $parts = array('resources', $scope, $file);
        $relative = implode('/', array_filter($parts));
        $source = $this->resolve('public', $file, $scope, true);
        $target = new SplFileInfo($this->getPathManager()->get('media', $relative));

        if ($target->isFile() && (($source !== false) && ($source->getMTime() <= $target->getMTime()))) {
            return new PublicFileInfo($relative, 'media');
        }

        if (($source === false) || !$source->isFile() || !$source->isReadable()) {
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
     * Resolve a file
     *
     * @param string $file
     */
    public function resolve($type, $file, $scope = null, $asFileInfo = false)
    {
        if (!$scope && ($scope !== false) && (strpos($file, '::') !== false)) {
            list($scope, $file) = explode('::', $file, 2);
        }

        if (!$scope || !isset($this->locations[$scope][$type])) {
            return false;
        }

        $path = $this->locations[$scope][$type] . '/' . ltrim($file);

        if ($asFileInfo) {
            $path = new SplFileInfo($path);
        }

        return $path;
    }
}