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
    protected $allowNull = false;

    /**
     * Is a primary key
     *
     * @var bool
     */
    protected $isPrimaryKey = false;

    /**
     * Is identity (sequence or auto increment)
     *
     * @var bool
     */
    protected $isIdentity = false;

    /**
     * Set the name of this attribute
     *
     * @param string $name
     * @return \rampage\orm\entity\type\Attribute
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
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


}