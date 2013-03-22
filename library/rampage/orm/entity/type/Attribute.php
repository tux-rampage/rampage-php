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

namespace rampage\orm\entity\type;

/**
 * Entity attribute
 */
class Attribute
{
    /**
     * Attribute name
     *
     * @var string
     */
    protected $name = null;

    /**
     * Attribute type
     *
     * @var string
     */
    protected $type = 'string';

    /**
     * Allow null values
     *
     * @var bool
     */
    protected $nullable = false;

    /**
     * Is a primary key
     *
     * @var bool
     */
    protected $identifier = false;

    /**
     * Is identity (sequence or auto increment)
     *
     * @var bool
     */
    protected $generated = false;

    /**
     * Virtual attribute flag
     *
     * @var bool
     */
    protected $virtual = false;

    /**
     * Construct
     *
     * @param string $name
     * @param string $type
     * @param bool $primary
     * @param bool $generated
     * @param bool $nullable
     */
    public function __construct($name, $type = null, $primary = false, $generated = false, $nullable = false)
    {
        $this->name = (string)$name;
        $this->identifier = (bool)$primary;
        $this->generated = (bool)$generated;
        $this->nullable = (bool)$nullable;

        if ($type) {
            $this->type = (string)$type;
        }
    }

    /**
     * Get the name of this attribute
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Attribute type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Check null allowed
     *
     * @return bool
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * Check if it is an identifier
     *
     * @return bool
     */
    public function isIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Retruns if this attribute should be generated automatically
     *
     * @return bool
     */
    public function isGenerated()
    {
        return $this->generated;
    }

    /**
     * Returns whether the attribute is virtual
     *
     * @return boolean
     */
    public function isVirtual()
    {
        return $this->virtual;
    }
}
