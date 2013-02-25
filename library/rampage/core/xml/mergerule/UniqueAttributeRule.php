<?php
/**
 * This is part of @application_name@
 * Copyright (c) 2010 Axel Helmert
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
 * @package   @package_name@
 * @copyright Copyright (c) 2010 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\xml\mergerule;

use rampage\core\xml\SimpleXmlElement;
use rampage\core\xml\exception;

/**
 * Allow multible siblings and use an attribute as identifier
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2010 Axel Helmert
 */
class UniqueAttributeRule extends AbstractPathRule
{
    /**
     * regex for validating names
     */
    const ATTRIBUTE_REGEX = '[a-z][a-z0-9_]*';

    /**
     * Attribute name
     *
     * @var string
     */
    private $attribute = null;

    /**
     * Flag for being strict
     *
     * @var bool
     */
    private $strict = false;

    /**
     * Construct
     *
     * @param string $regex
     * @param string $attribute
     */
    public function __construct($regex = null, $attribute = null, $strict = null)
    {
        parent::__construct($regex);

        if ($attribute) {
            $this->setAttribute($attribute);
        }

        if ($strict !== null) {
            $this->setIsStrict(true);
        }
    }

    /**
     * validate attribute name
     *
     * @param string $name
     * @return bool
     */
    protected function isValidAttribute($name)
    {
        $pattern = '~^' . self::ATTRIBUTE_REGEX . '$~i';
        $result = @preg_match($pattern, $name);
        return (bool)$result;
    }

    /**
     * set attribute name
     *
     * @param string $name
     * @throws \rampage\core\xml\InvalidArgumentException
     */
    public function setAttribute($name)
    {
        if (!$this->isValidAttribute($name)) {
            throw new exception\InvalidArgumentException(sprintf(
                'Invalid attribute name "%s"', $name
            ));
        }

        $this->attribute = $name;
        return $this;
    }

    /**
     * get attribute name
     *
     * @return string|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set strict flag
     *
     * being strict means that the given attribute is required for merging
     *
     * @param bool $flag
     */
    public function setIsStrict($flag = true)
    {
        $this->strict = (bool)$flag;
        return $this;
    }

    /**
     * Flag: ist strict?
     *
     * @return bool
     */
    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * (non-PHPdoc)
     * @see code/rampage/core/xml/rampage\core\xml.MergeRuleInterface::__invoke()
     */
    public function __invoke(SimpleXmlElement $parent, SimpleXmlElement $newChild, &$affected = null)
    {
        $attribute = $this->getAttribute();
        $name = $newChild->getName();

        if (!$attribute) {
            throw new exception\LogicException('No Attribute specified');
        }

        if (!$this->isPathMatching($parent, $name)) {
            return false;
        }

        if (!isset($newChild[$attribute])) {
            if ($this->isStrict()) {
                throw new exception\RuntimeException(
                    sprintf('New child does not have required attribute "%s"',
                    $attribute
                ));
            }

            return SimpleXmlElement::MERGE_APPEND;
        }

        $id = (string)$newChild[$attribute];
        $result = SimpleXmlElement::MERGE_APPEND;

        foreach ($parent->{$name} as $child) {
            if ((string)$child[$attribute] == $id) {
                $affected = $child;
                $result = SimpleXmlElement::MERGE_REPLACE;

                break;
            }
        }

        return $result;
    }
}