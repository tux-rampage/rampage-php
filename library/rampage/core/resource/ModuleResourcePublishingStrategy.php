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

use rampage\core\PathManager;
use rampage\core\ModuleRegistry;
use rampage\core\Module;
use rampage\core\exception;

use DirectoryIterator;
use SplFileInfo;

/**
 * Static resource publishing strategy
 */
class ModuleResourcePublishingStrategy
{
    /**
     * @var \rampage\core\PathManager
     */
    private $pathManager = null;

    /**
     * @var \rampage\core\ModuleRegistry
     */
    private $modules = null;

    /**
     * @param ModuleRegistry $moduleRegistry
     * @param PathManager $pathManager
     */
    public function __construct(ModuleRegistry $moduleRegistry, PathManager $pathManager)
    {
        $this->pathManager = $pathManager;
        $this->modules = $moduleRegistry;
    }

    /**
     * @param string|array $pathConfig
     * @return string|boolean
     */
    protected function getPublicPath($pathConfig)
    {
        if (!is_array($pathConfig)) {
            return $pathConfig . '/public';
        }

        if (isset($pathConfig['public'])) {
            return $pathConfig['public'];
        }

        if (!isset($pathConfig['base'])) {
            return false;
        }

        return $pathConfig['base'] . '/public';
    }

    /**
     * @param string $source
     * @param string $target
     */
    protected function copyDir($source, $target)
    {
        $targetInfo = new SplFileInfo($target);
        if (!$targetInfo->isDir() && !mkdir($target, 0777, true)) {
            throw new exception\RuntimeException('Filed to create directory: ' . $target);
        }

        /* @var $file \SplFileInfo */
        $iterator = new DirectoryIterator($source);
        foreach ($iterator as $file) {
            if (in_array($file->getFilename(), array('.', '..'))) {
                continue;
            }

            if ($file->isDir()) {
                $this->copyDir($file->getPathname(), $target . '/' . $file->getFilename());
                continue;
            }

            echo 'Publishing file: ' . $target . '/' . $file->getFilename(), " ...\n";
            copy($file->getPathname(), $target . '/' . $file->getFilename());
        }

        return $this;
    }

    /**
     * Publish resources
     *
     * @param Module $module
     * @param string $targetDir
     */
    protected function publishResources(Module $module, $targetDir)
    {
        $config = $module->getConfig();

        if (!isset($config['rampage']['resources'])) {
            return $this;
        }

        echo 'Publishing resource files for module "', $module->getName(), '" ...', "\n";

        foreach ($config['rampage']['resources'] as $scope => $paths) {
            $sourceDir = $this->getPublicPath($paths);
            if (!$sourceDir) {
                continue;
            }

            $segments = array($targetDir, 'module', $scope);
            $path = implode('/', array_filter($segments));

            $this->copyDir($sourceDir, $path);
        }

        return $this;
    }

    /**
     * @param Module $module
     * @param string $targetDir
     */
    protected function publishThemeFiles(Module $module, $targetDir)
    {
        $config = $module->getConfig();

        if (!isset($config['rampage']['themes'])) {
            return $this;
        }

        echo 'Publishing theme files for module "', $module->getName(), '" ...', "\n";

        foreach ($config['rampage']['themes'] as $name => $themeConfig) {
            if (!isset($themeConfig['paths']) || !($sourceDir = $this->getPublicPath($themeConfig['paths']))) {
                continue;
            }

            $segments = array($targetDir, 'theme', $name);
            $path = implode('/', array_filter($segments));

            $this->copyDir($sourceDir, $path);
        }

        return $this;

    }

    /**
     * Publish all module resources
     *
     * @param string $targetDir
     */
    public function publish($targetDir = null)
    {
        if (!$targetDir) {
            $targetDir = $this->pathManager->get('public', 'static');
        }

        foreach ($this->modules as $name => $module) {
            $this->publishResources($module, $targetDir);
            $this->publishThemeFiles($module, $targetDir);
        }

        return $this;
    }
}