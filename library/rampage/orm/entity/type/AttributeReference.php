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

namespace rampage\orm\entity\type;

use rampage\orm\exception\InvalidArgumentException;
use rampage\orm\exception\RuntimeException;
use rampage\orm\exception\LogicException;

/**
 * Attribute reference definition
 */
class AttributeReference
{
    /**
     * @var bool
     */
    private $literal = false;

    /**
     * @var string
     */
    private $attribute = null;

    /**
     * @var string
     */
    private $referencedAttribute = null;

    /**
     * Construct
     *
     * @param string $attributeOrLiteral
     * @param string $referencedAttribute
     * @param string $isLiteral
     */
    public function __construct($attributeOrLiteral, $referencedAttribute, $isLiteral = false)
    {
        if (!$isLiteral) {
            $attributeOrLiteral = (string)$attributeOrLiteral;

            if ($attributeOrLiteral == '') {
                throw new InvalidArgumentException('The attribute name must not be empty');
            }
        }

        $referencedAttribute = (string)$referencedAttribute;
        if ($referencedAttribute == '') {
            throw new InvalidArgumentException('The referenced attribute name must not be empty');
        }

        $this->literal = (bool)$isLiteral;
        $this->attribute = $attributeOrLiteral;
        $this->referencedAttribute = $referencedAttribute;
    }

    /**
     * Check if the reference is a literal expression
     *
     * @return boolean
     */
    public function isLiteral()
    {
        return $this->literal;
    }

    /**
     * Returns the literal expression
     *
     * @throws RuntimeException
     * @return mixed
     */
    public function getLiteral()
    {
        if (!$this->isLiteral()) {
            throw new LogicException('This attribute reference is not literal');
        }

        return $this->attribute;
    }

    /**
     * Returns the local attribute name
     *
     * @throws LogicException
     * @return string
     */
    public function getAttribute()
    {
        if ($this->isLiteral()) {
            throw new LogicException('This attribute reference is literal');
        }

        return $this->attribute;
    }

    /**
     * Returns the referenced attribute name
     *
     * @return string
     */
    public function getReferencedAttribute()
    {
        return $this->referencedAttribute;
    }
}