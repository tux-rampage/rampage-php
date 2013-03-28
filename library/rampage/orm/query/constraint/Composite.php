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

namespace rampage\orm\query\constraint;

use rampage\orm\exception\InvalidArgumentException;
use IteratorAggregate;
use ArrayIterator;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Composite constraint
 */
class Composite implements IteratorAggregate, CompositeInterface
{
    /**
     * Composition type
     *
     * @var string
     */
    private $type = self::TYPE_AND;

    /**
     * Child constraints
     *
     * @var array
     */
    private $constraints = array();

    /**
     * Construct
     *
     * @param string $type
     */
    public function __construct($type = null)
    {
        if ($type !== null) {
            $this->setType($type);
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
        $type = array_shift($args);
        return new static($type);
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
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new ArrayIterator($this->constraints);
    }

	/**
     * Set the constraint type
     *
     * @param string $type
     * @throws InvalidArgumentException
     * @return \rampage\orm\query\constraint\Composite
     */
    public function setType($type)
    {
        $type = strtolower($type);
        if (!in_array($type, array(self::TYPE_AND, self::TYPE_OR))) {
            throw new InvalidArgumentException('Invalid constraint composition type: ' . $type);
        }

        $this->type = $type;
        return $this;
    }

    /**
     * Add a constraint
     *
     * @param ConstraintInterface $constraint
     * @param string $name
     * @return \rampage\orm\query\constraint\Composite $this
     */
    public function add(ConstraintInterface $constraint, $name = null)
    {
        if (is_string($name)) {
            $this->constraints[$name] = $constraint;
        } else {
            $this->constraints[] = $constraint;
        }

        return $this;
    }

    /**
     * Add multiple constraints
     *
     * @param array|\Traversable $constraints
     */
    public function addMultiple($constraints)
    {
        foreach ($constraints as $name => $constraint) {
            $this->add($constraint, $name);
        }

        return $this;
    }

    /**
     * Remove a named constraint
     *
     * @param string $name
     * @return \rampage\orm\query\constraint\Composite $this
     */
    public function remove($name)
    {
        unset($this->constraints[$name]);
        return $this;
    }

    /**
     * Clear constraints
     *
     * @return \rampage\orm\query\constraint\Composite $this Fluent interface.
     */
    public function clear()
    {
        $this->constraints = array();
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\query\constraint\CompositeInterface::isEmpty()
     */
    public function isEmpty()
    {
        return (count($this->constraints) == 0);
    }
}