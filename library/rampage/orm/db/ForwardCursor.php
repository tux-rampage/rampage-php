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

namespace rampage\orm\db;

use Iterator;
use Zend\Db\Adapter\Driver\ResultInterface;
use rampage\orm\exception\InvalidArgumentException;

/**
 * Forward cursor
 */
class ForwardCursor implements Iterator
{
    /**
     * Database result
     *
     * @var \Zend\Db\Adapter\Driver\ResultInterface
     */
    private $result = null;

    /**
     * Item Factory
     *
     * @var callable
     */
    private $factory = null;

    /**
     * Current item
     *
     * @var object
     */
    private $current = null;

    /**
     * Construct
     *
     * @param ResultInterface $result
     * @param callable $factory
     */
    public function __construct(ResultInterface $result, $factory)
    {
        $this->result = $result;
        $this->setFactory($factory);
    }

    /**
     * Set the item factory
     *
     * This factory must be callable and consume the raw data array
     * as first parameter.
     *
     * @param callable $factory
     * @throws InvalidArgumentException
     * @return \rampage\orm\db\ForwardCursor
     */
    public function setFactory($factory)
    {
        if (!is_callable($factory)) {
            throw new InvalidArgumentException('Expecting a callable as item factory');
        }

        $this->factory = $factory;
        return $this;
    }

    /**
     * Returns the result set
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function getResult()
    {
        return $this->result;
    }

    /**
     * Create the item via factory
     *
     * @param array $data
     * @return object
     */
    protected function createItem($data)
    {
        if (!$data) {
            return false;
        }

        return call_user_func($this->factory, $data);
    }

    /**
     * @see Iterator::current()
     */
    public function current()
    {
        if ($this->current !== null) {
            return $this->current;
        }

        $this->current = $this->createItem($this->getResult()->current());
        return $this->current;
    }

    /**
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->getResult()->key();
    }

    /**
     * @see Iterator::next()
     */
    public function next()
    {
        $this->current = null;
        $this->getResult()->next();
    }

    /**
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        // Just ensure the first item is fetched and valid() will return the correct state
        $this->current();
    }

    /**
     * @see Iterator::valid()
     */
    public function valid()
    {
        return $this->getResult()->valid();
    }
}