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

use SplObjectStorage;

/**
 * Transaction aggergate
 */
class TransactionAggregate implements TransactionInterface
{
    /**
     * @var \SplObjectStorage
     */
    private $transactions;

    /**
     * @var bool
     */
    private $active = false;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->transactions = new SplObjectStorage();
    }

    /**
     * @param TransactionInterface $tansaction
     * @return self
     */
    public function addTransaction(TransactionInterface $transaction)
    {
        if ($this->transactions->contains($transaction)) {
            return $this;
        }

        if ($this->active) {
            $transaction->start();
        }

        $this->transactions->attach($transaction);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\simpleorm\TransactionInterface::commit()
     */
    public function commit()
    {
        if (!$this->active) {
            return $this;
        }

        foreach ($this->transactions as $transaction) {
            $transaction->commit();
        }

        $this->active = false;
        return $this;
    }

	/**
     * {@inheritdoc}
     * @see \rampage\simpleorm\TransactionInterface::rollback()
     */
    public function rollback()
    {
        if (!$this->active) {
            return $this;
        }

        foreach ($this->transactions as $transactions) {
            $transactions->rollback();
        }

        $this->active = false;
        return $this;
    }

	/**
     * {@inheritdoc}
     * @see \rampage\simpleorm\TransactionInterface::start()
     */
    public function start()
    {
        if ($this->active) {
            return $this;
        }

        foreach ($this->transactions as $transaction) {
            $transaction->start();
        }

        $this->active = true;
        return $this;
    }
}
