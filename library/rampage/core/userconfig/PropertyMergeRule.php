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

namespace rampage\core\userconfig;

use rampage\core\xml\mergerule\AbstractPathRule;
use rampage\core\xml\SimpleXmlElement;
use rampage\core\xml\exception\RuntimeException;

/**
 * Config property merge rule
 */
class PropertyMergeRule extends AbstractPathRule
{
    /**
     * (non-PHPdoc)
     * @see \rampage\core\xml\MergeRuleInterface::__invoke()
     */
    public function __invoke(SimpleXmlElement $parent, SimpleXmlElement $newChild, &$affected = null)
    {
        $name = $newChild->getName();
        if (!$this->isPathMatching($parent, $name)) {
            return false;
        }

        if (!isset($newChild['name'])) {
            throw new RuntimeException(sprintf(
                'The child "%s" to merge does not have required name attribute',
                $newChild->getPath()
            ));
        }

        $id = (string)$newChild['name'];
        $domain = (string)$newChild['domain'];
        $result = SimpleXmlElement::MERGE_APPEND;

        $domainXpath = (isset($newChild['domain']))? "@domain = {$parent->quoteXpathValue($domain)}" : 'not(@domain)';
        $xpath = "./{$name}[@name = {$parent->quoteXpathValue($id)} and $domainXpath]";

        foreach ($parent->xpath($xpath) as $child) {
            $affected = $child;
            $result = SimpleXmlElement::MERGE_REPLACE;

            break;
        }

        return $result;
    }
}
