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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\query;

/**
 * Persistence query
 */
interface QueryInterface
{
    /**
     * Return the entity type name to query
     *
     * @return string
     */
    public function getEntityType();

    /**
     * Set the entity type to query
     *
     * @param string $name
     * @return \rampage\orm\query\QueryInterface $this Fluent interface
     */
    public function setEntityType($name);

    /**
     * Add a constraint to match
     *
     * @param Constraint $constraint
     * @return \rampage\orm\query\QueryInterface $this Fluent interface
     */
    public function matches(constraint\ConstraintInterface $constraint);

    /**
     * Limit the result set size
     *
     * @param int $limit
     * @return \rampage\orm\query\QueryInterface $this Fluent interface
     */
    public function limit($limit);

    /**
     * Set the current offset
     *
     * @param int $offset
     * @return \rampage\orm\query\QueryInterface $this Fluent interface
     */
    public function offset($offset);

    /**
     * set the current order
     *
     * @param string|array $attribute
     * @param string $direction
     * @return \rampage\orm\query\QueryInterface $this Fluent interface
     */
    public function order($attribute, $direction = null);

    /**
     * Returns all constraints
     *
     * @return \rampage\orm\query\constraint\CompositeInterface
     */
    public function getConstraints();

    /**
     * Return the current order by attributes
     *
     * @return array
     */
    public function getOrder();

    /**
     * Return the current limit
     *
     * @return int
     */
    public function getLimit();

    /**
     * Return the current offset
     *
     * @return int
     */
    public function getOffset();
}