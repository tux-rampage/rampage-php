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

use Zend\Authentication\Result;

/**
 * Aggregated authentication result
 */
class AggregatedAuthResult extends Result
{
    /**
     * Resulting adapter
     *
     * @var string
     */
    protected $adapterType = null;

    /**
     * Construct
     *
     * @param string $code
     * @param bool $identity
     * @param array $messages
     */
    public function __construct($adapterType, $code, $identity = null, array $messages = array())
    {
        if ($code instanceof AggregatedAuthResult) {
            $identity = $code->getIdentity();
            $messages = $code->getMessages();
            $code = $code->getCode();
        }

        $this->adapterType = $adapterType;
        parent::__construct($code, $identity, $messages);
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Authentication\Result::isValid()
     */
    public function isValid()
    {
        if (!$this->adapterType) {
            return false;
        }

        return parent::isValid();
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\Authentication\Result::getIdentity()
     */
    public function getIdentity()
    {
        return array(
            'adaptertype' => $this->adapterType,
            'identity' => $this->identity
        );
    }
}