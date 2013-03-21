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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\db;

use rampage\orm\db\adapter\AdapterAggregate;
use rampage\core\model\Config as UserConfig;
use rampage\core\ModuleRegistry;

/**
 * Module based db setup
 */
class ModuleSetup extends Setup
{
    /**
     * @var ModuleRegistry
     */
    private $moduleRegistry = null;

    /**
     * The module name to set up
     *
     * @var string
     */
    protected $moduleName = null;

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\Setup::__construct()
     */
    public function __construct(AdapterAggregate $adapterAggregate, UserConfig $userConfig, ModuleRegistry $moduleRegistry)
    {
        $this->moduleRegistry = $moduleRegistry;
        parent::__construct($adapterAggregate, $userConfig);
    }

    /**
     * Set the module name
     *
     * @param string $name
     */
    public function setModuleName($name)
    {
        $this->moduleName = $name;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\Setup::getScriptLocation()
     */
    protected function getScriptLocation()
    {
        if (!$this->location) {
            $dir = $this->moduleRegistry->getModule($this->moduleName, true)->getResourcePath('db', 'schema');
            $this->setScriptLocation($dir);
        }

        return parent::getScriptLocation();
    }
}