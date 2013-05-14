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

/**
 * ID strategy annotation
 */
class IdentifierStrategyAnnotation extends AbstractAnnotation
{
    /**
     * @var string
     */
    private $class = null;

    /**
     * @param string $content
     */
    public function __construct($content)
    {
        $this->initialize($content);
    }

    /**
     * @see \rampage\simpleorm\metadata\annotation\AnnotationInterface::getKeyword()
     */
    public function getKeyword()
    {
        return 'idstrategy';
    }

    /**
     * @see \Zend\Code\Annotation\AnnotationInterface::initialize()
     */
    public function initialize($content)
    {
        if (false === ($pos = strpos($content, '('))) {
            $this->class = substr($content, 0, $pos);
            $content = trim(substr($content, $pos));
            $this->parseContent(trim($content, '()'), array());
        } else {
            $this->class = $content;
        }
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->params;
    }
}