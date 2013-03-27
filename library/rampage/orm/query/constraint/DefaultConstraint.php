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

namespace rampage\orm\query\constraint;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Default constraint
 */
class DefaultConstraint implements ConstraintInterface
{
    const TYPE_COMPARE = 'compare';
    const TYPE_EQUALS = 'equals';
    const TYPE_NOTEQUALS = 'notequals';
    const TYPE_LIKE = 'like';
    const TYPE_NOTLIKE = 'notlike';
    const TYPE_ISNULL = 'isnull';
    const TYPE_NOTNULL = 'notnull';
    const TYPE_IN = 'in';

    /**
     * The default constraint
     *
     * @var string
     */
    protected $type = null;

    /**
     * The attribute
     *
     * @var string
     */
    protected $attribute = null;

    /**
     * Compare Value
     *
     * @var string
     */
    protected $value = null;

    /**
     * Compare operator
     *
     * @var string
     */
    protected $operator = '=';

    /**
     * Construct
     *
     * @param string $type
     * @param string $attribute
     * @param string $value
     */
    public function __construct($type, $attribute, $value = null, $operator = null)
    {
        $this->type = $type;
        $this->attribute = $attribute;
        $this->value = $value;

        if ($operator) {
            $this->operator = $operator;
        }
    }

    /**
     * Factory
     *
     * @param string $name
     * @param array $args
     * @param ServiceLocatorInterface $serviceLocator
     */
    public static function factory($name, array $args, ServiceLocatorInterface $serviceLocator)
    {
        @list($attribute, $value, $operator) = $args;
        return new static($name, $attribute, $value, $operator);
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\query\constraint\ConstraintInterface::getType()
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Compare attribute
     *
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Compare value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the compare operator
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }
}