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
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Attribute metadata
 */
class Attribute
{
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOL = 'bool';
    const TYPE_DATETIME = 'datetime';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $identifier = false;

    /**
     * @var \Zend\Stdlib\Hydrator\Strategy\StrategyInterface
     */
    private $hydrationStrategy = null;

    /**
     * @var array
     */
    protected $validTypes = array(
        self::TYPE_BOOL,
        self::TYPE_DATETIME,
        self::TYPE_FLOAT,
        self::TYPE_INT,
        self::TYPE_STRING
    );

    /**
     * @param string $name The name of this attribute
     * @param string $field The field name
     * @param string $type The type of this attribute. The default type is string.
     */
    public function __construct($name, $field = null, $type = null)
    {
        $name = trim((string)$name);
        $field = trim((string)$field);
        $type = $type?: self::TYPE_STRING;

        if ($name == '') {
            throw new exception\InvalidArgumentException('The attribute name must not be empty.');
        }

        if (!in_array($type, $this->validTypes)) {
            throw new exception\InvalidArgumentException(sprintf(
                'Invalid attribute type "%s" for attribute "%s".',
                $type, $name
            ));
        }

        $this->name = $name;
        $this->field = ($field == '')? $name : $field;
        $this->type = $type;
    }

    /**
     * @return \Zend\Stdlib\Hydrator\Strategy\StrategyInterface
     */
    public function getHydrationStrategy()
    {
        return $this->hydrationStrategy;
    }

    /**
     * @param string|StrategyInterface $strategy
     */
    public function setHydrationStrategy($strategy)
    {
        if (!is_string($strategy) && !($strategy instanceof StrategyInterface)) {
            throw new exception\InvalidArgumentException('Invalid hydration strategy. Must be a string or implement Zend\Stdlib\Hydrator\Strategy\StrategyInterface.');
        }

        if (is_string($strategy)) {
            $strategy = strtr($strategy, '.', '\\');
        }

        $this->hydrationStrategy = $strategy;
        return $this;
    }

	/**
     * @param string $flag
     * @return self
     */
    public function setIsIdentifier($flag = true)
    {
        $this->identifier = (bool)$flag;
        return $this;
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
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function isIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param array $data
     */
    public static function factory(array $data)
    {
        if (!isset($data['name'])) {
            throw new exception\InvalidArgumentException('Invalid attribute data');
        }

        $name = $data['name'];
        $field = (isset($data['field']))? $data['field'] : $name;
        $type = (isset($data['type']))? $data['type'] : null;

        $attribute = new static($name, $field, $type);

        if (isset($data['identifier'])) {
            $attribute->setIsIdentifier($data['identifier']);
        }

        if (isset($data['hydration_strategy'])) {
            $attribute->setHydrationStrategy($data['hydration_strategy']);
        }

        return $attribute;
    }
}
