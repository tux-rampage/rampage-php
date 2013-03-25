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

namespace rampage\orm\query;

use Traversable;
use rampage\orm\exception\InvalidArgumentException;
use rampage\orm\query\constraint\ConstraintLocator;
use rampage\orm\query\constraint\DefaultConstraint;
use rampage\orm\query\constraint\ConstraintInterface;

/**
 * Persistence query implementation
 */
class Query implements QueryInterface
{
    /**
     * Entity name
     *
     * @var string
     */
    private $entity = null;

    /**
     * Constraint chain
     *
     * @var \rampage\orm\query\constraint\CompositeInterface
     */
    protected $constraints = null;

    /**
     * Constraint locator
     *
     * @var \rampage\orm\query\constraint\ConstraintLocator
     */
    protected $constraintLocator = null;

    /**
     * Result limit
     *
     * @var int
     */
    protected $limit = null;

    /**
     * Result offset
     *
     * @var int
     */
    protected $offset = null;

    /**
     * Result set ordering
     *
     * @var array
     */
    protected $orders = array();

    /**
     * Construct
     *
     * @param string $entity
     */
    public function __construct(ConstraintLocator $locator, $entity = null)
    {
        $this->setEntityType($entity);
        $this->constraints = new constraint\Composite();
        $this->constraintLocator = $locator;
    }

    /**
     * Map unknown method to constraint locator
     *
     * @param string $method
     * @param array $args
     * @return \rampage\orm\query\constraint\ConstraintInterface
     */
    public function __call($method, $args)
    {
        $options = (isset($args[0]) && is_array($args[0]))? $args[0] : array();
        return $this->getConstraintLocator()->get($method, $options);
    }

    /**
     * Constraint locator
     *
     * @return \rampage\orm\query\constraint\ConstraintLocator
     */
    protected function getConstraintLocator()
    {
        return $this->constraintLocator;
    }

    /**
     * Equals constraint
     *
     * @param string $attribute
     * @param string $value
     * @return \rampage\orm\query\constraint\DefaultConstraint
     */
    public function equals($attribute, $value)
    {
        return $this->getConstraintLocator()->get(DefaultConstraint::TYPE_EQUALS, array(
            'attribute' => $attribute,
            'value' => $value
        ));
    }

    /**
     * Lower constraint
     *
     * @param string $attribute
     * @param string $value
     * @return \rampage\orm\query\constraint\ConstraintInterface
     */
    public function lower($attribute, $value)
    {
        return $this->compare($attribute, $value, '<');
    }

    /**
     * Greater constraint
     *
     * @param string $attribute
     * @param string $value
     * @return \rampage\orm\query\constraint\ConstraintInterface
     */
    public function greater($attribute, $value)
    {
        return $this->compare($attribute, $value, '>');
    }

    /**
     * Lower or equal constraint
     *
     * @param string $attribute
     * @param string $value
     * @return \rampage\orm\query\constraint\ConstraintInterface
     */
    public function lowerEqual($attribute, $value)
    {
        return $this->compare($attribute, $value, '<=');
    }

    /**
     * Greate or equal constraint
     *
     * @param string $attribute
     * @param string $value
     * @return \rampage\orm\query\constraint\ConstraintInterface
     */
    public function greaterEqual($attribute, $value)
    {
        return $this->compare($attribute, $value, '>=');
    }

    /**
     * Not equals constraint
     *
     * @param string $attribute
     * @param string $value
     * @return \rampage\orm\query\constraint\DefaultConstraint
     */
    public function notEquals($attribute, $value)
    {
        return $this->getConstraintLocator()->get(DefaultConstraint::TYPE_NOTEQUALS, array(
            'attribute' => $attribute,
            'value' => $value
        ));
    }

    /**
     * Equals constraint
     *
     * @param string $attribute
     * @param string $value
     * @return \rampage\orm\query\constraint\DefaultConstraint
     */
    public function like($attribute, $value)
    {
        return $this->getConstraintLocator()->get(DefaultConstraint::TYPE_LIKE, array(
            'attribute' => $attribute,
            'value' => $value
        ));
    }

    /**
     * not like constraint
     *
     * @param string $attribute
     * @param string $value
     * @return \rampage\orm\query\constraint\DefaultConstraint
     */
    public function notLike($attribute, $value)
    {
        return $this->getConstraintLocator()->get(DefaultConstraint::TYPE_NOTLIKE, array(
            'attribute' => $attribute,
            'value' => $value
        ));
    }

    /**
     * Equals constraint
     *
     * @param string $attribute
     * @return \rampage\orm\query\constraint\DefaultConstraint
     */
    public function isNull($attribute)
    {
        return $this->getConstraintLocator()->get(DefaultConstraint::TYPE_ISNULL, array(
            'attribute' => $attribute,
        ));
    }

    /**
     * Equals constraint
     *
     * @param string $attribute
     * @return \rampage\orm\query\constraint\DefaultConstraint
     */
    public function notNull($attribute)
    {
        return $this->getConstraintLocator()->get(DefaultConstraint::TYPE_NOTNULL, array(
            'attribute' => $attribute,
        ));
    }

    /**
     * Compare constraint
     *
     * @param string $attribute
     * @param mixed $value
     * @param string $operator
     * @return \rampage\orm\query\constraint\DefaultConstraint
     */
    public function compare($attribute, $value, $operator = null)
    {
        return $this->getConstraintLocator()->get(DefaultConstraint::TYPE_COMPARE, array(
            'attribute' => $attribute,
            'value' => $value,
            'operator' => $operator,
        ));
    }

    /**
     * In constraint
     *
     * @param string $attribute
     * @param array $values
     * @return \rampage\orm\query\constraint\DefaultConstraint
     */
    public function in($attribute, $values)
    {
        return $this->getConstraintLocator()->get(DefaultConstraint::TYPE_IN, array(
            'attribute' => $attribute,
            'value' => $values
        ));
    }

    /**
     * Composite constraint
     *
     * @return \rampage\orm\query\constraint\CompositeInterface
     */
    public function coposite($type = constraint\Composite::TYPE_AND)
    {
        if (!in_array(strtolower($type), array('and', 'or'))) {
            throw new InvalidArgumentException('Invalid constraint composite type: ' . $type);
        }

        return $this->getConstraintLocator()->get($type);
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\query\QueryInterface::getConstraints()
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\query\QueryInterface::getEntityType()
     */
    public function getEntityType()
    {
        return $this->entity;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\query\QueryInterface::getLimit()
     */
    public function getLimit()
    {
        return $this->limit;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\query\QueryInterface::getOffset()
     */
    public function getOffset()
    {
        return $this->offset;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\query\QueryInterface::getOrder()
     */
    public function getOrder()
    {
        return $this->orders;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\query\QueryInterface::limit()
     */
    public function limit($limit)
    {
        $limit = ($limit === null)? null : (int)$limit;
        if ($limit < 0) {
            throw new InvalidArgumentException('Invalid limit specified: ' . $limit);
        }

        $this->limit = $limit;
        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\query\QueryInterface::matches()
     */
    public function matches(ConstraintInterface $constraint)
    {
        $this->getConstraints()->add($constraint);
        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\query\QueryInterface::offset()
     */
    public function offset($offset)
    {
        $offset = ($offset === null)? null : (int)$offset;
        if ($offset < 0) {
            throw new InvalidArgumentException('Invalid offset specified: ' . $offset);
        }

        $this->offset = $offset;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\query\QueryInterface::order()
     */
    public function order($attribute, $direction = null)
    {
        if (is_array($attribute) || ($attribute instanceof Traversable)) {
            foreach ($attribute as $key => $value) {
                if (is_string($key)) {
                    $this->order($key, $value);
                    continue;
                }

                $this->order($value, $direction);
            }

            return $this;
        }

        $direction = ($direction)? strtolower($direction) : null;
        if (($direction !== null) && !in_array($direction, array('asc', 'desc'))) {
            throw new InvalidArgumentException('Invalid order direction: ' . $direction);
        }

        $this->orders[] = array($attribute, $direction);
        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\query\QueryInterface::setEntityType()
     */
    public function setEntityType($name)
    {
        return $this->entity = (string)$name;
    }
}