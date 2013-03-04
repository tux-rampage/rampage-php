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

use rampage\core\model\Config as UserConfig;
use rampage\orm\db\adapter\AdapterAggregate;
use rampage\orm\db\ddl\CreateTable;
use rampage\orm\db\ddl\ColumnDefinition;
use rampage\orm\db\ddl\IndexDefinition;

class DdlSetup
{
    /**
     * Adapter aggregate
     *
     * @var AdapterAggregate
     */
    private $adapterAggregate = null;

    /**
     * User config
     *
     * @var UserConfig
     */
    private $infoTable = null;

    /**
     * Construct
     *
     * @param UserConfig $config
     * @param AdapterAggregate $adapter
     */
    public function __construct(AdapterAggregate $adapterAggregate, UserConfig $userConfig)
    {
        $this->infoTable = (string)$userConfig->getConfigValue('orm.db.schemainfo', 'repositries', false);
        $this->adapterAggregate = $adapterAggregate;
    }

    /**
     * Adapter aggregate
     *
     * @return \rampage\orm\db\adapter\AdapterAggregate
     */
    public function getAdapterAggregate()
    {
        return $this->adapterAggregate;
    }

    /**
     * User config
     *
     * @return \rampage\core\model\Config
     */
    protected function getUserConfig()
    {
        return $this->userConfig;
    }

    /**
     * Schema info table
     *
     * @return string
     */
    public function getSchemaInfoTable()
    {
        return $this->getAdapterAggregate()->getPlatform()->formatIdentifier($this->infoTable);
    }

    /**
     * Set schema info table
     *
     * @param string $table
     * @return \rampage\orm\db\DdlSetup
     */
    public function setInfoTable($table)
    {
        $this->infoTable = $table;
        return $this;
    }

    /**
     * Schema info table
     *
     * @return bool
     */
    public function hasSchemaInfoTable()
    {
        $tables = $this->getAdapterAggregate()->metadata()->getTableNames();
        return in_array($this->getSchemaInfoTable(), $tables);
    }

    /**
     * create schema info table
     */
    public function createSchemaInfoTable()
    {
        $ddl = new CreateTable($this->getSchemaInfoTable());
        $ddl->addColumn($ddl->column('repository', ColumnDefinition::TYPE_VARCHAR, 128)->setIsNullable(false))
            ->addColumn($ddl->column('version', ColumnDefinition::TYPE_INT)->setIsNullable(false))
            ->setPrimaryKey(array('repository'));

    }
}