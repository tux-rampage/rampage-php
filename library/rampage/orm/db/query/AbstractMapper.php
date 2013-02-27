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

namespace rampage\orm\db\query;

// Query deps
use rampage\orm\query\Query;
use rampage\orm\query\constraint\ConstraintInterface;
use rampage\orm\query\constraint\CompositeInterface as ConstraintCompositeInterface;
use rampage\orm\query\constraint\DefaultConstraint;

// Exceptions
use rampage\orm\exception\DependencyException;
use rampage\orm\exception\RuntimeException;

// SQL deps
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Predicate\Operator;
use rampage\orm\query\QueryInterface;
use rampage\orm\db\platform\PlatformInterface;
use Zend\Db\Sql\Expression;

/**
 * Default query mapper
 */
abstract class AbstractMapper implements MapperInterface
{
    /**
     * Platform
     *
     * @var \rampage\orm\db\platform\PlatformInterface
     */
    private $platform = null;

    /**
     * Current query
     *
     * @var Query
     */
    private $query = null;

    /**
     * Operator map
     *
     * @var string
     */
    protected $operatorMap = array(
        '=' => Operator::OP_EQ,
        '>' => Operator::OP_GT,
        '>=' => Operator::OP_GTE,
        '<' => Operator::OP_LT,
        '<=' => Operator::OP_LTE,
        '!=' => Operator::OP_NE,
    );

    /**
     * Construct
     *
     * @param PlatformInterface $platform
     */
    public function __construct(PlatformInterface $platform = null)
    {
        if ($platform) {
            $this->setPlatform($platform);
        }
    }

    /**
     * Returns the current platform instance
     *
     * @return \rampage\orm\db\platform\PlatformInterface
     */
    protected function getPlatform()
    {
        if (!$this->platform) {
            throw new DependencyException('Missing platform instance');
        }

        return $this->platform;
    }

    /**
     * Current query
     *
     * @return \rampage\orm\query\Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set the current query
     *
     * @param Query $query
     * @return \rampage\orm\db\query\AbstractMapper
     */
    protected function setQuery(QueryInterface $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Set the platform instance to use
     *
     * @param \rampage\orm\db\platform\PlatformInterface $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
        return $this;
    }

    /**
     * Returns the identifier for the given attribute
     *
     * @param string $attribute
     * @return string
     */
    protected function getAttributeIdentifier($attribute)
    {
        $field = $attribute;
        $mapper = $this->getPlatform()->getFieldMapper($this->getQuery()->getEntityType());

        if ($mapper) {
            $field = $mapper->mapAttribute($attribute);
        }

        return $field;
    }

    /**
     * Prepare select from part
     *
     * @param Query $query
     * @param Select $select
     * @return \rampage\orm\db\query\AbstractMapper
     */
    protected function prepareSelectFrom(Select $select)
    {
        $select->from($this->getPlatform()->getTable($this->getQuery()->getEntityType()));
        return $this;
    }

    /**
     * Prepare additional select joins
     *
     * @param Query $query
     * @param Select $select
     * @return \rampage\orm\db\query\AbstractMapper
     */
    protected function prepareAdditionalSelectJoins(Select $select)
    {
        return $this;
    }

    /**
     * Prepare the like attribute
     *
     * @param string $attribute
     * @param string $identifier
     * @return string
     */
    protected function prepareLikeIdentifier($attribute, $identifier)
    {
        return $identifier;
    }

    /**
     * Prepare the like value
     *
     * @param string $value
     * @return string
     */
    protected function prepareLikeValue($value)
    {
        return $value;
    }

    /**
     * Map constraints
     *
     * @param ConstraintInterface $constraint
     * @param Select $select
     */
    protected function mapConstraint(ConstraintInterface $constraint, Predicate $predicate)
    {
        if ($constraint instanceof ConstraintCompositeInterface) {
            $this->mapConstraintComposite($constraint, $predicate);
            return $this;
        }

        $type = $constraint->getType();
        $constraintMapper = $this->getPlatform()->getConstraintMapper($type);
        if ($constraintMapper) {
            $constraintMapper->map($constraint, $predicate, $this);
        }

        $method = 'map' . ucfirst($type) . 'Constraint';
        if (is_callable(array($this, $method))) {
            $this->$method($constraint, $predicate);
            return $this;
        }

        if ($constraint instanceof DefaultConstraint) {
            $identifier = $this->getAttributeIdentifier($constraint->getAttribute());
            $value = $constraint->getValue();

            switch ($type) {
                case DefaultConstraint::TYPE_COMPARE:
                    $operator = $constraint->getOperator();
                    if (!isset($this->operatorMap[$operator])) {
                        throw new RuntimeException('Unknown compare operator: ' . $operator);
                    }

                    $sqlOperator = $this->operatorMap[$operator];
                    $predicate = new Operator($identifier, $sqlOperator, $value);

                    break;

                case DefaultConstraint::TYPE_EQUALS:
                    $predicate->equalTo($identifier, $value);
                    break;

                case DefaultConstraint::TYPE_IN:
                    $predicate->in($identifier, $value);
                    break;

                case DefaultConstraint::TYPE_ISNULL:
                    $predicate->isNull($identifier);
                    break;

                case DefaultConstraint::TYPE_LIKE:
                    $identifier = $this->prepareLikeIdentifier($constraint->getAttribute(), $identifier);
                    $predicate->like($identifier, $this->prepareLikeValue($value));
                    break;

                case DefaultConstraint::TYPE_NOTEQUALS:
                    $predicate->notEqualTo($identifier, $value);
                    break;

                case DefaultConstraint::TYPE_NOTLIKE:
                    $identifier = $this->prepareLikeIdentifier($constraint->getAttribute(), $identifier);
                    $value = $this->prepareLikeValue($value);

                    $predicate->addPredicate(new Operator($identifier, 'NOT LIKE', $value));
                    break;

                case DefaultConstraint::TYPE_NOTNULL:
                    $predicate->isNotNull($identifier);
                    break;

                default:
                    throw new RuntimeException('Could not map unsupported constraint type: ' . $type);
            }
        } else {
            throw new RuntimeException('Could not map unsupported constraint type: ' . $type);
        }

        return $this;
    }

    /**
     * Map composite constraints
     *
     * @param CompositeInterface $constraints
     * @return \Zend\Db\Sql\Predicate\Predicate
     */
    protected function mapConstraintComposite(ConstraintCompositeInterface $constraints, Predicate $parentPredicate = null)
    {
        $predicate = ($parentPredicate)? $parentPredicate->nest() : new Predicate();

        foreach ($constraints as $constraint) {
            $this->mapConstraint($constraint, $predicate);
        }

        return $predicate;
    }

    /**
     * Prepare where
     *
     * @param Select $select
     */
    protected function prepareSelectWhere(Select $select)
    {
        $constraints = $this->getQuery()->getConstraints();
        if ($constraints->isEmpty()) {
            return $this;
        }

        $select->where($this->mapConstraintComposite($constraints));
        return $this;
    }

    /**
     * Prepare the select order by
     *
     * @param Select $select
     */
    protected function prepareSelectOrder(Select $select)
    {
        foreach ($this->getQuery()->getOrder() as $order) {
            @list($attribute, $direction) = $order;

            $identifier = $this->getAttributeIdentifier($attribute);
            $select->order($identifier . ' ' . $order);
        }

        return $this;
    }

    /**
     * Prepare select limitations
     *
     * @param Select $select
     */
    protected function prepareSelectLimit(Select $select)
    {
        $limit = $this->getQuery()->getLimit();
        $offset = $this->getQuery()->getOffset();

        if ($limit !== null) {
            $select->limit($limit);
        }

        if ($offset !== null) {
            $select->offset($offset);
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\query\MapperInterface::mapToSelect()
     */
    public function mapToSelect(QueryInterface $query, Select $select)
    {
        $this->setQuery($query);

        $this->prepareSelectFrom($select)
            ->prepareAdditionalSelectJoins($select)
            ->prepareSelectWhere($select)
            ->prepareSelectOrder($select)
            ->prepareSelectLimit($select);

        return $select;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\query\MapperInterface::mapToSizeSelect()
     */
    public function mapToSizeSelect(Query $query, Select $select)
    {
        $sizeSelect = clone $select;
        $this->mapToSelect($query, $sizeSelect);

        $sizeSelect->reset(Select::COLUMNS)
            ->reset(Select::GROUP)
            ->reset(Select::LIMIT)
            ->reset(Select::OFFSET)
            ->reset(Select::ORDER);

        $sizeSelect->columns(array(
            'size' => new Expression('COUNT(*)')
        ));

        return $sizeSelect;
    }
}
