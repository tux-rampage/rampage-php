<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2013 Axel Helmert
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

namespace rampage\simpleorm;

/**
 * ID strategy interface
 */
class IdentifierStrategyInterface
{
    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager);

    /**
     * @param string $table
     */
    public function setTable($table);

    /**
     * @param array $fields
     */
    public function setFields(array $fields);

    /**
     * Check record existence
     *
     * @param string $data
     * @return bool
     */
    public function exists($data);

    /**
     * Prepare insert
     *
     * @param array $data
     * @return bool
     */
    public function prepareInsert(&$data);

    /**
     * Prepare update
     *
     * @param array $data
     * @return \Zend\Db\Sql\Predicate\PredicateInterface
     */
    public function getWherePredicate(&$data);
}