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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm\metadata;

use rampage\db\metadata\Metadata as DatabaseMetadata;

/**
 * Database driver
 */
class DatabaseDriver implements DriverInterface
{
    /**
     * @var DatabaseMetadata
     */
    private $dbMetadata = null;

    /**
     * @see \rampage\simpleorm\metadata\DriverInterface::hasEntityDefintion()
     */
    public function hasEntityDefintion($name)
    {
        return false;
    }

    /**
     * @param Metadata $metadata
     * @return \rampage\db\metadata\Metadata
     */
    protected function getDbMetadata(Metadata $metadata)
    {
        return $metadata->getEntityManager()->getDbMetadata();
    }

    /**
     * @see \rampage\simpleorm\metadata\DriverInterface::loadEntityDefintion()
     */
    public function loadEntityDefintion($name, Metadata $metadata, Entity $entity = null)
    {
        if (!$entity || !$entity->getTable()) {
            return $this;
        }

        $dbMetadata = $this->getDbMetadata($metadata);
        $tableName = $entity->getTable();

        if (!in_array($tableName, $dbMetadata->getTableNames()) || !($columns = $dbMetadata->getColumns($tableName))) {
            return $this;
        }

        /* @var $column \Zend\Db\Metadata\Object\ColumnObject */
        foreach ($columns as $column) {
            $field = $column->getName();
            $attribute = $entity->getAttributes()->getAttributeByField($field);

            $type = $metadata->getEntityManager()
                ->getTypeMap()
                ->mapDbType($column->getDataType());

            if ($attribute instanceof Attribute) {
                if (!$attribute->getType()) {
                    $attribute->setType($type);
                }

                continue;
            }

            $name = strtolower($field);
            $attribute = new Attribute($name, $field, $type);

            $entity->getAttributes()->add($attribute);
        }

        return $this;
    }
}
