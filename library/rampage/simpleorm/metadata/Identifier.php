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
use rampage\simpleorm\IdentifierStrategyInterface;
use rampage\simpleorm\StaticIdentifierStrategy;
use rampage\simpleorm\AutoincrementIdentifierStrategy;

/**
 * Identifier
 */
class Identifier extends AttributeCollection
{
    /**
     * @var IdentifierStrategyInterface
     */
    private $strategy = null;

    /**
     * @var Entity
     */
    private $entity = null;

    /**
     * @param Entity $entity
     */
    public function __construct(Entity $entity)
    {
        parent::__construct(array());

        $this->entity = $entity;

        /* @var $attribute Attribute */
        foreach ($entity->getAttributes() as $attribute) {
            if (!$attribute->isIdentifier()) {
                continue;
            }

            $this->append($attribute);
        }
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $fields = array();
        foreach ($this as $attribute) {
            $fields[] = $attribute->getField();
        }

        return $fields;
    }

    /**
     * Create identifier strategy
     *
     * @return IdentifierStrategyInterface
     */
    protected function createStrategy()
    {
        if (!$this->isValid()) {
            throw new exception\LogicException('Cannot create identifier strategy without valid identifier');
        }

        if ($this->isMultiKey() || !$this->getAttribute()->isAutoIncrement()) {
            return new StaticIdentifierStrategy();
        }

        return new AutoincrementIdentifierStrategy();
    }

    /**
     * @param IdentifierStrategyInterface $strategy
     * @return self
     */
    public function setStrategy(IdentifierStrategyInterface $strategy)
    {
        $strategy->setFields($this->getFields());
        $strategy->setTable($this->entity->getTable()); // FIXME: entity reference
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * Identifier strategy
     */
    public function getStrategy()
    {
        if (!$this->strategy) {
            $this->setStrategy($this->createStrategy());
        }

        return $this->strategy;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return ($this->count() > 0);
    }

    /**
     * @return boolean
     */
    public function isMultiKey()
    {
        return ($this->count() > 1);
    }

    /**
     * @return \rampage\simpleorm\metadata\Attribute
     */
    public function getAttribute()
    {
        foreach ($this as $attribute) {
            return $attribute;
        }

        throw new exception\AttributeNotFoundException('No attribute defined for identifier');
    }
}
