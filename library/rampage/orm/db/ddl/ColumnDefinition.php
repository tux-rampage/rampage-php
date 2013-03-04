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
use rampage\orm\exception\RuntimeException;
/**
 * Column definition
 */
class ColumnDefinition extends NamedDefintion
{
    const TYPE_INT = 'INT';
    const TYPE_VARCHAR = 'VARCHAR';
    const TYPE_FLOAT = 'DECIMAL';
    const TYPE_TEXT = 'TEXT';
    const TYPE_BLOB = 'BLOB';
    const TYPE_CLOB = 'CLOB';
    const TYPE_BOOL = 'BOOL';
    const TYPE_ENUM = 'ENUM';

    /**
     * The column type
     *
     * @var string
     */
    private $type = null;

    /**
     * Enum values
     *
     * @var array
     */
    private $values = array();

    /**
     * Column size definition
     *
     * @var int
     */
    private $size = null;

    /**
     * Column precision
     *
     * @var string
     */
    private $precision = null;

    /**
     * Allow null flag
     *
     * @var bool
     */
    private $nullable = false;

    /**
     * Default column value
     *
     * @var mixed
     */
    private $default = null;

    /**
     * Flag if the column is an identity (auto increment)
     *
     * @var bool
     */
    private $identity = false;

    /**
     * Construct
     *
     * @param string $name
     * @param string $type
     * @param int|array $sizeOrValues
     */
    public function __construct($name, $type = null, $sizeOrValues = null)
    {
        if ($type === null) {
            $type = static::TYPE_VARCHAR;
        }

        $this->setName($name);
        $this->type = $type;

        if ($sizeOrValues !== null) {
            if ($type == static::TYPE_ENUM) {
                $this->setValues($sizeOrValues);
            } else {
                $this->setSize($sizeOrValues);
            }
        }
    }

	/**
     * column type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

	/**
     * enum values
     *
     * @return multitype:
     */
    public function getValues()
    {
        return $this->values;
    }

	/**
     * column size
     *
     * @return number
     */
    public function getSize($required)
    {
        if ($required && !$this->size) {
            throw new RuntimeException('Missing size for column type ' . $this->getType());
        }

        return $this->size;
    }

	/**
     * column precision
     *
     * @return int
     */
    public function getPrecision($required)
    {
        if ($required && !$this->precision) {
            throw new RuntimeException('Missing precision for column type ' . $this->getType());
        }

        return $this->precision;
    }

	/**
     * Nullable flag
     *
     * @return boolean
     */
    public function isNullable()
    {
        return $this->nullable;
    }

	/**
     * Default value
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Returns the identity
     *
     * @return boolean
     */
    public function isIdentity()
    {
        return (bool)$this->identity;
    }

    /**
     * Returns the sequence name
     *
     * @return boolean
     */
    public function getSequenceName()
    {
        if (!is_string($this->identity)) {
            return false;
        }

        return $this->identity;
    }

	/**
     * Set the column type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = (string)$type;
        return $this;
    }

	/**
     * Set the enum values
     *
     * @param array $values
     */
    public function setValues(array $values)
    {
        $this->values = $values;
        return $this;
    }

	/**
     * Set the size
     *
     * @param number $size
     */
    public function setSize($size)
    {
        $size = ($size === null)? null : (int)$size;
        if (($size !== null) && ($size < 1)) {
            throw new InvalidArgumentException('Invalid column size: ' . $size);
        }

        $this->size = $size;
        return $this;
    }

	/**
     * Set the precision
     *
     * @param int $precision
     */
    public function setPrecision($precision)
    {
        $precision = ($precision === null)? null : (int)$precision;
        if (($precision !== null) && ($precision < 1)) {
            throw new InvalidArgumentException('Invalid column precision: ' . $precision);
        }

        $this->precision = (int)$precision;
        return $this;
    }

	/**
     * Set nullable flag
     *
     * @param boolean $flag
     */
    public function setIsNullable($flag)
    {
        $this->nullable = (bool)$flag;
        return $this;
    }

	/**
     * Set the default value
     *
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

	/**
     * Set identity name/flag (auto increment)
     *
     * It's recommented to provide a string which will then be used as
     * sequence name (if required by the platform)
     *
     * @param boolean $identity
     */
    public function setIdentity($identity)
    {
        if (!is_string($identity)) {
            $identity = (bool)$identity;
        }

        $this->identity = $identity;
        return $this;
    }
}