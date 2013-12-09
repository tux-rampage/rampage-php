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

namespace rampage\core;

/**
 * Module info
 */
abstract class AbstractModule
{
    private $manifest = null;

    /**
     * @param ModuleManifest $manifest
     */
    public function __construct(ModuleManifest $manifest)
    {
        $this->manifest = $manifest;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws exception\DependencyException
     */
    private function fetchManifestConfig($key, $ensureArray = false)
    {
        if (!$this->manifest) {
            throw new exception\DependencyException('Failed to load module manifest data without ModuleManifest instance');
        }

        $config = $this->manifest->toArray($key);
        if ($ensureArray && !is_array($config)) {
            $config = array();
        }

        return $config;
    }

    /**
     * @return \rampage\core\ModuleManifest
     */
    protected function getModuleManifest()
    {
        return $this->manifest;
    }

    /**
     * @param string $staticFile
     * @return array
     */
    protected function fetchConfigArray($staticFile = null)
    {
        if ($staticFile === null) {
            $staticFile = $this->manifest->getModulePath('conf/module.config.php');
        }

        if (is_readable($staticFile)) {
            return include $staticFile;
        }

        return $this->fetchManifestConfig('application_config', true);
    }

    /**
     * @param string $staticFile
     * @return array
     * @return \rampage\core\mixed
     */
    protected function fetchAutoloadConfigArray($staticFile = null)
    {
        if ($staticFile === null) {
            $staticFile = $this->manifest->getModulePath('conf/autoload.config.php');
        }

        if (is_readable($staticFile)) {
            return include $staticFile;
        }

        return $this->fetchManifestConfig('autoloader_config', true);
    }
}
