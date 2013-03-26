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
 * Attribute join reference
 */
class AttributeJoinReference extends Attribute
{
    /**
     * Reference name
     *
     * @var string
     */
    protected $reference = null;

    /**
     * @see \rampage\orm\entity\type\Attribute::__construct()
     */
    public function __construct($name, $reference = null, $type = null)
    {
        $this->reference = (string)$reference;
        parent::__construct($name, $type, false, false, true);
    }

	/**
     * @see \rampage\orm\entity\type\Attribute::isGenerated()
     */
    public function isGenerated()
    {
        return false;
    }

	/**
     * @see \rampage\orm\entity\type\Attribute::isIdentifier()
     */
    public function isIdentifier()
    {
        return false;
    }

	/**
     * @see \rampage\orm\entity\type\Attribute::isVirtual()
     */
    public function isVirtual()
    {
        return true;
    }

    /**
     * Returns the reference name for this attribute
     *
     * @return string
     */
    public function getReference()
    {
        if ($this->reference == '') {
            $this->reference = $this->getName();
        }

        return $this->reference;
    }
}