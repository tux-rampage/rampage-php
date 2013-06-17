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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm\hydration;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Integer strategy
 */
class PrimitiveStrategy implements StrategyInterface
{
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOL = 'bool';

    protected static $allowedTypes = array(
        self::TYPE_BOOL,
        self::TYPE_FLOAT,
        self::TYPE_INT,
        self::TYPE_STRING
    );

    /**
     * @var string
     */
    private $type = self::TYPE_STRING;

    /**
     * @param string
     */
    public function __construct($type)
    {

        if (!in_array($type, static::$allowedTypes)) {

        }

        $this->type = $type;
    }

    /**
     * @see \Zend\Stdlib\Hydrator\Strategy\StrategyInterface::hydrate()
     */
    public function hydrate($value)
    {
        if ($value === null) {
            return null;
        }

        switch ($this->type) {
            case self::TYPE_BOOL:
                return (bool)$value;

            case self::TYPE_FLOAT:
                return (float)$value;

            case self::TYPE_INT:
                return (int)$value;
        }

        return (string)$value;
    }

    /**
     * @see \Zend\Stdlib\Hydrator\Strategy\StrategyInterface::extract()
     */
    public function extract($value)
    {
        if (($value !== null) && ($this->type === self::TYPE_BOOL)) {
            return ($value)? 1 : 0;
        }

        return $this->hydrate($value);
    }



}