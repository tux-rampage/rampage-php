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

namespace rampage\core\modules;

use rampage\core\xml\Config;
use rampage\core\ModuleRegistry;
use rampage\core\PathManager;
use SplFileInfo;

/**
 * Aggregated XML config
 */
abstract class AggregatedXmlConfig extends Config
{
    /**
     * Module registry
     *
     * @var \rampage\core\ModuleRegistry
     */
    private $registry = null;

    /**
     * Path manager
     *
     * @var \rampage\core\PathManager
     */
    private $paths = null;

    /**
     * Construct
     *
     * @param \rampage\core\ModuleRegistry $registry
     * @param \rampage\core\PathManager $pathManager
     */
    public function __construct(ModuleRegistry $registry, PathManager $pathManager)
    {
        $this->setModuleRegistry($registry);
        $this->setPathManager($pathManager);
    }

    /**
     * Returns the module registry
     *
     * @return \rampage\core\ModuleRegistry
     */
    protected function getModuleRegistry()
    {
        return $this->registry;
    }

    /**
     * Path manager
     *
     * @return \rampage\core\PathManager
     */
    protected function getPathManager()
    {
        return $this->paths;
    }

    /**
     * File name relative to module path
     *
     * @return string
     */
    abstract protected function getModuleFilename();

    /**
     * Global file name
     *
     * @return string
     */
    abstract protected function getGlobalFilename();

    /**
     * Module registry
     *
     * @param \rampage\core\ModuleRegistry $registry
     * @return \rampage\core\modules\AggregatedXmlConfig
     */
    public function setModuleRegistry(ModuleRegistry $registry)
    {
        $this->registry = $registry;
        return $this;
    }

    /**
     * Path manager
     *
     * @param PathManager $paths
     * @return \rampage\core\modules\AggregatedXmlConfig
     */
    public function setPathManager(PathManager $paths)
    {
        $this->paths = $paths;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::_init()
     */
    protected function _init()
    {
        $this->setXml('<config></config>');

        /* @var $module \rampage\core\Module */
        foreach ($this->getModuleRegistry() as $module) {
            $file = $module->getModulePath($this->getModuleFilename(), true);
            if (!$file->isFile() || !$file->isReadable()) {
                continue;
            }

            $ref = new Config($file->getPathname());
            $this->merge($ref, true);

            unset($ref);
        }

        $global = $this->getPathManager()->get('etc', $this->getGlobalFilename());
        $info = new SplFileInfo($global);

        if (!$global || !$info->isFile() || !$info->isReadable()) {
            return $this;
        }

        $ref = new Config($global);
        $this->merge($ref, true);
        unset($ref);

        return $this;
    }
}