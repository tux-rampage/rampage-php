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
 * Transaction aggergate
 */
class TransactionAggregate implements TransactionInterface
{
    /**
     * @param TransactionInterface $tansaction
     * @return self
     */
    public function addTransaction(TransactionInterface $tansaction)
    {
        // TODO Implement feature
        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\simpleorm\TransactionInterface::commit()
     */
    public function commit()
    {
        // TODO Auto-generated method stub
        return $this;
    }

	/**
     * {@inheritdoc}
     * @see \rampage\simpleorm\TransactionInterface::rollback()
     */
    public function rollback()
    {
        // TODO Auto-generated method stub
        return $this;
    }

	/**
     * {@inheritdoc}
     * @see \rampage\simpleorm\TransactionInterface::start()
     */
    public function start()
    {
        // TODO Auto-generated method stub
        return $this;
    }
}
