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

namespace rampage\simpleorm\metadata;

use rampage\simpleorm\exception;
use IteratorAggregate;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Entity metadata
 */
class Entity implements IteratorAggregate
{
    /**
     * @var string
     */
    private $name = null;

    /**
     * @var string
     */
    private $class = null;

    /**
     * @var string
     */
    private $table = null;

    /**
     * @var AttributeCollection
     */
    private $attributes = null;

    /**
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
    private $hydrator = null;

    /**
     * @var Identifier
     */
    private $identifier = null;

    /**
     * @param string $name
     * @param string $table
     * @param array $attributes
     */
    public function __construct($name, $table, array $attributes = array())
    {
        $this->name = $name;
        $this->table = $table;
        $this->class = strtr($name, '.', '\\');

        $this->attributes = new AttributeCollection($attributes);
    }

    /**
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param HydratorInterface $hydrator
     * @return self
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * @return \rampage\simpleorm\metadata\AttributeCollection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @param string $name
     * @return \rampage\simpleorm\metadata\Attribute
     */
    public function getAttribute($name)
    {
        if (!isset($this->attributes[$name])) {
            throw new exception\AttributeNotFoundException('No such attribute: ' . $name);
        }

        return $this->attributes[$name];
    }

    /**
     * @param Identifier $identifier
     * @return self
     */
    public function setIdentifier(Identifier $identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Returns the identifier for this entity
     *
     * @return Identifier
     */
    public function getIdentifier()
    {
        if ($this->identifier) {
            return $this->identifier;
        }

        $identifier = new Identifier($this);
        $this->setIdentifier($identifier);

        return $identifier;
    }
}