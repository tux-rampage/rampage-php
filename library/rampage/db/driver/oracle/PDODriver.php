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

namespace rampage\db\driver\oracle;

use Zend\Db\Adapter\Driver\Pdo\Pdo;
use Zend\Db\Adapter\Driver\Pdo\Statement;
use Zend\Db\Adapter\Driver\Pdo\Result;

/**
 * Oracle PDO driver for ZF DB Adapter
 */
class PDODriver extends Pdo
{
    /**
     * Schema name
     *
     * @var string
     */
    protected $currentSchema = null;

	/**
     * (non-PHPdoc)
     * @see \Zend\Db\Adapter\Driver\Pdo\Pdo::__construct()
     */
    public function __construct($connection, Statement $statementPrototype = null, Result $resultPrototype = null, $features = self::FEATURES_DEFAULT)
    {
        if (is_array($connection)) {
            if (isset($connection['hostname'])) {
                $tns = '(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)'
                     . '(HOST=' . $connection['hostname'] . ')';

                if (isset($connection['port'])) {
                    $tns .= '(PORT=' . $connection['port'] . ')';
                } else {
                    $tns .= '(PORT=1521)';
                }

                if (isset($connection['service_name'])) {
                    $connectData = 'SERVICE_NAME=' . $connection['service_name'];
                } else {
                    $connectData = 'SID=' . $connection['database'];
                }

                $tns .= '))(CONNECT_DATA=(' . $connectData . ')))';
            } else {
                $tns = $connection['database'];
            }

            if (isset($connection['charset'])) {
                $tns .= ';charset=' . $connection['charset'];
            }

            unset($connection['hostname']);
            unset($connection['port']);
            $connection['database'] = $tns;

            // Use our own connection instance
            $connection = new Connection($connection);
        }

        parent::__construct($connection, $statementPrototype, $resultPrototype, $features);
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\Db\Adapter\Driver\Pdo\Pdo::getDatabasePlatformName()
     */
    public function getDatabasePlatformName($nameFormat = self::NAME_FORMAT_CAMELCASE)
    {
        return 'Oracle';
    }}