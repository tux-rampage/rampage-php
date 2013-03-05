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

use rampage\core\ObjectManagerInterface;
use rampage\core\ModuleRegistry;
use rampage\orm\ConfigInterface;
use rampage\orm\repository\InstallableRepositoryInterface;

/**
 * Repository supporting setups
 */
abstract class AbstractSetupRepository extends AbstractRepository implements InstallableRepositoryInterface
{
    /**
     * Module registry
     *
     * @var ModuleRegistry
     */
    private $moduleRegistry = null;

    /**
     * Construct
     */
    public function __construct(ObjectManagerInterface $objectManager, ConfigInterface $config, ModuleRegistry $moduleRegistry, $name = null)
    {
        $this->moduleRegistry = $moduleRegistry;
        parent::__construct($objectManager, $config, $name);
    }

    /**
     * Setup resource name
     *
     * @return NULL
     */
    abstract protected function getSetupResourceName();

    /**
     * @return \rampage\core\ModuleRegistry
     */
    protected function getModuleRegistry()
    {
        return $this->moduleRegistry;
    }

    /**
     * Setup instance
     *
     * @return SetupInterface
     */
    protected function getSetupInstance()
    {
        if (!($name = $this->getSetupResourceName())) {
            return null;
        }

        $schemaLocation = $this->getModuleRegistry()->getModule('mmd.zps')->getModulePath('res/db/schema', true);
        if (!$schemaLocation->isDir()) {
            return null;
        }

        $setup = $this->getObjectManager()->newInstance('rampage.orm.db.Setup', array(
            'adapterAggregate' => $this->getWriteAggregate()
        ));

        if ($setup instanceof SetupInterface) {
            $setup->setName($name);
            $setup->setScriptLocation($schemaLocation->getPathname());
        }

        return $setup;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\repository\InstallableRepositoryInterface::setup()
     */
    public function setup()
    {
        $setup = $this->getSetupInstance();
        if (!$setup instanceof SetupInterface) {
            return $this;
        }

        $setup->install();
        return $this;
    }
}