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

use DirectoryIterator;
use rampage\core\model\Config as UserConfig;
use rampage\orm\db\adapter\AdapterAggregate;
use rampage\orm\db\ddl\CreateTable;
use rampage\orm\db\ddl\ColumnDefinition;
use rampage\core\exception\RuntimeException;
use rampage\orm\db\ddl\DefinitionInterface;
use rampage\orm\db\ddl\AlterTable;
use rampage\orm\db\ddl\DropTable;

class Setup implements SetupInterface
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
     * Info table was just created
     *
     * @var bool
     */
    private $infoTableCreated = false;

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
    public function setName($name)
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
    protected function getScriptFiles()
    {
        if ($this->files !== null) {
            return $this->files;
        }

        $iterator = new DirectoryIterator($this->location);
        $files = array(
            'install' => array(),
            'upgrade' => array(),
        );

        /* @var $file \SplFileInfo */
        foreach ($iterator as $path => $file) {
            if ($file->isDir() || !preg_match('~ddl-(install|upgrade)-(\d+).php~', $file->getBasename(), $match)) {
                continue;
            }

            $type = $match[1];
            $revision = $match[2];
            $files[$type][$revision] = $file->getPathname();
        }

        krsort($files['install']);
        ksort($files['upgrade']);
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
     * SQL Builder
     *
     * @return \Zend\Db\Sql\Sql
     */
    public function sql()
    {
        return $this->getAdapterAggregate()->sql();
    }

    /**
     * Metadata
     *
     * @return \Zend\Db\Metadata\Metadata
     */
    public function metadata()
    {
        return $this->getAdapterAggregate()->metadata();
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
        if ($this->infoTableCreated) {
            return true;
        }

        $tables = $this->getAdapterAggregate()->metadata()->getTableNames();
        return in_array($this->getSchemaInfoTable(), $tables);
    }

    /**
     * create schema info table
     */
    protected function createSchemaInfoTable()
    {
        if ($this->hasSchemaInfoTable()) {
            return $this;
        }

        $ddl = $this->createTable($this->getSchemaInfoTable());
        $ddl->addColumn($ddl->column('repository', ColumnDefinition::TYPE_VARCHAR, 128)->setIsNullable(false))
            ->addColumn($ddl->column('revision', ColumnDefinition::TYPE_INT)->setIsNullable(true))
            ->setPrimaryKey(array('repository'));


        $this->infoTableCreated = true;
        $this->run($ddl);

        return $this;
    }

    /**
     * Create table ddl
     *
     * @param string $entityName
     * @return \rampage\orm\db\ddl\CreateTable
     */
    public function createTable($entityName)
    {
        return new CreateTable($entityName);
    }

    /**
     * Alter table definition
     *
     * @param string $entityName
     * @return \rampage\orm\db\ddl\AlterTable
     */
    public function alterTable($entityName)
    {
        return new AlterTable($entityName);
    }

    /**
     * Drop table definition
     *
     * @param string $entityName
     * @return \rampage\orm\db\ddl\DropTable
     */
    public function dropTable($entityName)
    {
        return new DropTable($entityName);
    }

    /**
     * Run DDL
     *
     * @param DefinitionInterface $ddl
     */
    public function run(DefinitionInterface $ddl)
    {
        $sql = $this->getPlatform()->getDDLRenderer()->renderDdl($ddl);
        $this->getAdapter()->query($sql);

        return $this;
    }

    /**
     * Check for required updates
     *
     * @return boolean
     */
    public function isUpdateRequired()
    {
        $latest = $this->getLatestRevision();
        if ($latest === null) {
            return false;
        }

        return ($latest > $this->getInstalledRevision());
    }

    /**
     * Latest revision
     *
     * @return NULL|mixed
     */
    public function getLatestRevision()
    {
        $files = $this->getScriptFiles();
        if (empty($files['install']) && empty($files['upgrade'])) {
            return null;
        }

        if (empty($files['upgrade'])) {
            $latest = (int)max(array_keys($files['install']));
        } else {
            $latest = (int)max(array_keys($files['upgrade']));
        }

        return $latest;
    }

    /**
     * Returns the current installed revision
     *
     * @return NULL|number
     */
    public function getInstalledRevision()
    {
        $this->createSchemaInfoTable();
        $table = $this->getPlatform()->getTable($this->getSchemaInfoTable());
        $field = $this->getPlatform()->formatIdentifier('repository');

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

    /**
     * Update the current revision
     *
     * @param int $version
     */
    protected function updateCurrentRevision($version, $installed)
    {
        $this->createSchemaInfoTable();
        $idField = $this->getPlatform()->formatIdentifier('repository');
        $versionField = $this->getPlatform()->formatIdentifier('revision');
        $data = array($versionField => $version);

        if ($installed === null) {
            $data[$idField] = $this->getName();
            $action = $this->sql()
                ->insert()
                ->values($data);
        } else {
            $where = array($idField => $this->getName());
            $action = $this->sql()
                ->update()
                ->set($data)
                ->where($where);
        }

        $this->sql()->prepareStatementForSqlObject($action)->execute();
        return $this;
    }

    /**
     * Install upgrades
     */
    public function install()
    {
        if (!$this->isUpdateRequired()) {
            return $this;
        }

        $current = $this->getInstalledRevision();
        $files = $this->getScriptFiles();

        if ($current === null) {
            reset($files['install']);
            $revision = key($files['install']);
            $file = current($files['install']);

            if ($revision && $file) {
                include $file;
                $this->updateCurrentRevision($revision, $current);
                $current = $revision;
            }
        }

        foreach ($files['upgrade'] as $revision => $file) {
            if (($current !== null) && ($revision <= $current)) {
                continue;
            }

            include $file;
            $this->updateCurrentRevision($revision, $current);

            $current = $revision;
        }

        return $this;
    }
}