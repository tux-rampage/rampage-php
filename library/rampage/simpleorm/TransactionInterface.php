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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm;

/**
 * Transaction interface
 */
interface TransactionInterface
{
    /**
     * The implementation should start the transaction
     *
     * @throws RuntimeException Should throw a runtime exception when the transaction was already started
     * @return self Should return $this for a fluent interface
     */
    public function start();

    /**
     * The implementation should commit the transaction
     *
     * @throws RuntimeException Should throw a runtime exception when the transaction was not started or already comitted
     * @return self Should return $this for a fluent interface
     */
    public function commit();

    /**
     * The implementation should rollback the transaction
     *
     * @throws RuntimeException Should throw a runtime exception when the transaction was not started or already comitted
     * @return self Should return $this for a fluent interface
     */
    public function rollback();
}
