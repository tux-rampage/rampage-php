<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2013 Axel Helmert
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

namespace rampage\simpleorm\metadata\annotation;

class IdentifierAnnotation extends AbstractAnnotation
{
    /**
     * @var IdentifierStrategyAnnotation
     */
    private $strategy = null;

    /**
     * @var array
     */
    protected $optionNames = array('auto', 'strategy');

    /**
     * @param bool $forClass
     */
    public function __construct($forClass = false)
    {
        if ($forClass) {
            array_unshift($this->optionNames, 'attribute');
        }
    }

    /**
     * @see \rampage\simpleorm\metadata\annotation\AnnotationInterface::getKeyword()
     */
    public function getKeyword()
    {
        return 'identifier';
    }

    /**
     * @see \Zend\Code\Annotation\AnnotationInterface::initialize()
     */
    public function initialize($content)
    {
        $this->parseContent($content, $this->optionNames);
    }

    /**
     * @return bool
     */
    public function isAutoIncrement()
    {
        return $this->toBool($this->getParam('auto', true));
    }

    /**
     * @return string|null
     */
    public function getAttribute()
    {
        return $this->getParam('attribute');
    }

    /**
     * @return string
     */
    public function getStrategy()
    {
        if ($this->strategy !== null) {
            return $this->strategy;
        }

        $strategy = $this->getParam('strategy', '');
        $this->strategy = new IdentifierStrategyAnnotation($strategy);

        return $this->strategy;
    }
}