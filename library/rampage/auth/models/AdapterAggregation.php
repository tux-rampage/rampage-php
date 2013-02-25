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
 * @package   rampage.auth
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\auth\models;

// SPL
use IteratorAggregate;
use ArrayIterator;

// Libs
use Zend\Authentication\Adapter\AdapterInterface as AuthAdapterInterface;
use Zend\Authentication\Result;
use Zend\Authentication\Exception\ExceptionInterface as AuthException;
use rampage\core\log\Logger;

/**
 * Adater aggregation
 */
class AdapterAggregation implements AuthAdapterInterface, IteratorAggregate
{
    /**
     * Adapters
     *
     * @var array
     */
    private $adapters = array();

    /**
     * Logger instance
     *
     * @var \rampage\core\log\Logger
     */
    private $logger = null;

    /**
     * Constructor
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logger instance
     *
     * @return \rampage\core\log\Logger
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * Add an authentication adapter
     *
     * @param AdapterInterface $adapter
     * @return \rampage\auth\models\AdapterAggregation
     */
    public function addAdapter(AdapterInterface $adapter)
    {
        $this->adapters[] = $adapter;
        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new ArrayIterator($this->adapters);
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\Authentication\Adapter\AdapterInterface::authenticate()
     */
    public function authenticate()
    {
        $result = null;

        /* @var $adapter \rampage\auth\models\AdapterInterface */
        foreach ($this->adapters as $adapter) {
            try {
                $result = $adapter->authenticate();

                if ($result->isValid()) {
                    return $result;
                }
            } catch (AuthException $e) {
                $this->getLogger()->err($e);
            }
        }

        return new Result(Result::FAILURE, false, array('Authentication failed'));
    }
}
