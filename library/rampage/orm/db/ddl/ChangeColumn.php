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

namespace rampage\orm\db\ddl;

use rampage\orm\exception\InvalidArgumentException;
/**
 * Change column definition
 */
class ChangeColumn extends ColumnDefinition
{
    const CHANGETYPE_ADD = 'add';
    const CHANGETYPE_DROP = 'drop';
    const CHANGETYPE_MODIFY = 'modify';

    /**
     * New name for this column
     *
     * @var string
     */
    private $newName = null;

    /**
     * Column position after
     *
     * @var string
     */
    private $after = null;

    /**
     * Place column at the beginning of the table
     *
     * After should be ignored by platform renderers
     *
     * @var bool
     */
    private $first = false;

    /**
     * Change type
     *
     * @var string
     */
    private $changeType = self::CHANGETYPE_ADD;

	/**
     * Returns the new column name
     *
     * NULL if the column should not be renamed
     *
     * @return string|null
     */
    public function getNewName()
    {
        return $this->newName;
    }

	/**
     * Returns the positioning column name
     *
     * @return string
     */
    public function getAfter()
    {
        return $this->after;
    }

    /**
     * Flag if the column should be placed at the beginning of the table
     *
     * If set to true the after definition should be ignored by pleatform implementation
     *
     * @return boolean
     */
    public function isFirst()
    {
        return $this->first;
    }

	/**
     * Set the new name if this column should be renamed
     *
     * @param string $newName
     */
    public function setNewName($newName)
    {
        $this->newName = ($newName)? (string)$newName : null;
        return $this;
    }

	/**
     * Set the column positioning
     *
     * @param string $after
     */
    public function setAfter($after)
    {
        $this->after = ($after)? (string)$after : null;
        return $this;
    }

    /**
     * Returns the change type
     *
     * @return string
     */
    public function getChangeType()
    {
        return $this->changeType;
    }

    /**
     * Set the change type
     *
     * @param string $changeType
     */
    public function setChangeType($changeType)
    {
        $changeType = strtolower($changeType);
        $allowed = array(
            static::CHANGETYPE_ADD,
            static::CHANGETYPE_DROP,
            static::CHANGETYPE_MODIFY
        );

        if (!in_array($changeType, $allowed)) {
            throw new InvalidArgumentException('Invalid change type: ' . $changeType);
        }

        $this->changeType = $changeType;
        return $this;
    }
}