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

/**
 * Index defintion
 */
class IndexDefinition extends NamedDefintion
{
    /**
     * Fields
     *
     * @var array
     */
    private $fields = array();

    /**
     * Unique index flag
     *
     * @var bool
     */
    private $unique = false;

    /**
     * Constructor
     *
     * @param string $name
     * @param array $fields
     * @param string $unique
     */
    public function __construct($name, array $fields, $unique = false)
    {
        $this->unique = (bool)$unique;
        $this->setName($name);

        foreach ($fields as $key => $field) {
            $order = null;

            if (is_string($key)) {
                $order = $field;
                $field = $key;
            }

            $this->addField($field, $order);
        }
    }

    /**
     * Add a field to this index
     *
     * @param string $field
     * @param string $order
     */
    public function addField($field, $order = null)
    {
        $this->fields[] = array($field, $order);
        return $this;
    }

	/**
     * Returns the field defintion
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

	/**
     * returns the unique flag
     *
     * @return bool
     */
    public function isUnique()
    {
        return $this->unique;
    }
}