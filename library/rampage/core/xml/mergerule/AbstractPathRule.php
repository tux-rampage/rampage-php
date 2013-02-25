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
use rampage\core\xml\MergeRuleInterface;
use rampage\core\Utils;

/**
 * Abstarct path mathing merge rule
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2010 Axel Helmert
 */
abstract class AbstractPathRule implements MergeRuleInterface
{
    /**
     * regex for validating names
     */
    const NAME_REGEX = '[a-z][a-z0-9_]*';

    /**
     * Regex for testing the path expression
     *
     * @var string
     */
    private $pathRegex = null;

    /**
     * Check if path is matching for the requested element
     *
     * @param xml\SimpleXmlElement $node
     * @param string $name
     * @return bool
     */
    protected function isPathMatching(SimpleXmlElement $node, $name)
    {
        $pathPattern = $this->getPathRegex();
        $childPath = $node->getPath() . '/' . $name;

        if (!$pathPattern) {
            return false;
        }

        return (bool)preg_match($pathPattern, $childPath);
    }

    /**
     * Constructor
     *
     * @param string $regex
     */
    public function __construct($regex = null)
    {
        if (is_array($regex) || ($regex instanceof \Traversable)) {
            Utils::setOptions($this, $regex);
            return;
        }

        $this->setPathRegex($regex);
    }

    /**
     * Set path expression
     *
     * @param string $regex
     * @throws \rampage\core\xml\InvalidArgumentException
     */
    public function setPathRegex($regex)
    {
        if ($regex === null) {
            $this->pathRegex = null;
            return $this;
        }

        $this->pathRegex = $regex;
        return $this;
    }

    /**
     * returns path pattern
     *
     * @return string|null
     */
    protected function getPathRegex()
    {
        return $this->pathRegex;
    }
}