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
use DirectoryIterator;
use Zend\Db\Sql\Where;
use rampage\core\exception\RuntimeException;

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
     * Location containing the files
     *
     * @var string
     */
    private $location = null;

    /**
     * script Files
     *
     * @var string[]
     */
    private $files = null;

    /**
     * resource name
     *
     * @var string
     */
    private $name = null;

    /**
     * Construct
     *
     * @param UserConfig $config
     * @param AdapterAggregate $adapter
     */
    public function __construct(AdapterAggregate $adapterAggregate, UserConfig $userConfig)
    {
        $this->infoTable = (string)$userConfig->getConfigValue('orm.db.schemainfo', 'repository_schema', false);
        $this->adapterAggregate = $adapterAggregate;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        if (!$this->getName()) {
            throw new RuntimeException('Missing ddl setup name');
        }

        return $this->name;
    }

	/**
     * @param string $name
     */
    protected function setName($name)
    {
        $this->name = (string)$name;
        return $this;
    }

	/**
     * Set the scripts directory
     *
     * @param string $dir
     */
    public function setScriptLocation($dir)
    {
        $this->location = $dir;
    }

    /**
     * Script files
     */
    public function getScriptFiles()
    {
        if ($this->files !== null) {
            return $this->files;
        }

        $iterator = new DirectoryIterator($this->location);
        $files = array();

        /* @var $file \SplFileInfo */
        foreach ($iterator as $file) {
            if ($file->isDir() || !preg_match('ddl-setup-(\d+).php', $file->getBasename(), $match)) {
                continue;
            }

            $revision = $match[1];
            $files[$revision] = $file->getPathname();
        }

        ksort($files);
        $this->files = $files;

        return $files;
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
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getAdapter()
    {
        return $this->getAdapterAggregate()->getAdapter();
    }

    /**
     * @return \rampage\orm\db\platform\PlatformInterface
     */
    public function getPlatform()
    {
        return $this->getAdapterAggregate()->getPlatform();
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
    protected function getSchemaInfoTable()
    {
        return $this->infoTable;
    }

    /**
     * Set schema info table
     *
     * @param string $table
     * @return \rampage\orm\db\DdlSetup
     */
    public function setSchemaInfoTable($table)
    {
        $this->infoTable = $table;
        return $this;
    }

    /**
     * Schema info table
     *
     * @return bool
     */
    protected function hasSchemaInfoTable()
    {
        $tables = $this->getAdapterAggregate()->metadata()->getTableNames();
        return in_array($this->getSchemaInfoTable(), $tables);
    }

    /**
     * create schema info table
     */
    public function createSchemaInfoTable()
    {
        if ($this->hasSchemaInfoTable()) {
            return $this;
        }

        $ddl = new CreateTable($this->getSchemaInfoTable());
        $ddl->addColumn($ddl->column('repository', ColumnDefinition::TYPE_VARCHAR, 128)->setIsNullable(false))
            ->addColumn($ddl->column('version', ColumnDefinition::TYPE_INT)->setIsNullable(true))
            ->setPrimaryKey(array('repository'));

        $sql = $this->getPlatform()->getDDLRenderer()->renderDdl($ddl);
        $this->getAdapter()->query($sql);

        return $this;
    }

    public function getCurrentRevision()
    {
        $this->createSchemaInfoTable();
        $field = $this->getPlatform()->formatIdentifier('version');

        $sql = $this->getAdapterAggregate()->sql();
        $select = $sql->select()
            ->from($this->getSchemaInfoTable())
            ->where(array($field => $this->getName()));

        $result = $sql->prepareStatementForSqlObject($select)
            ->execute()
            ->current();

        if (!$result) {
            return null;
        }

        $revision = (isset($result['revision']))? $result['revision'] : $result['REVISION'];
        return (int)$revision;
    }
}