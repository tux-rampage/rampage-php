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

namespace rampage\simpleorm\metadata\annotation;

use rampage\simpleorm\exception;
use rampage\core\Utils;
use Zend\Code\Annotation\Parser\ParserInterface;
use Zend\EventManager\EventInterface;

/**
 * Annotation parser
 */
class AnnotationParser implements ParserInterface
{
    /**
     * @var array
     */
    private $annotations = array();

    /**
     * @see \Zend\Code\Annotation\Parser\ParserInterface::onCreateAnnotation()
     */
    public function onCreateAnnotation(EventInterface $e)
    {
        $class = $e->getParam('class');
        if (!$class || !preg_match('~\\\\(simple)?orm:([a-zA-Z0-9_-]+)$~', $class, $m)) {
            return false;
        }

        $name = $m[2];
        if (!isset($this->annotations[$name])) {
            return false;
        }

        $content = $e->getParam('content');
        $content = trim($content, '()');
        $annotation = clone $this->annotations[$name];

        $annotation->initialize($content);
        return $annotation;
    }

	/**
     * @see \Zend\Code\Annotation\Parser\ParserInterface::registerAnnotation()
     */
    public function registerAnnotation($annotation)
    {
        if (!$annotation instanceof AnnotationInterface) {
            throw new exception\InvalidArgumentException(sprintf(
                '%s: expects an instance of rampage\simpleorm\metadata\annotation\AnnotationInterface; received "%s"',
                __METHOD__, Utils::getPrintableTypeName($annotation)
            ));
        }

        $this->annotations[$annotation->getKeyword()] = $annotation;
        return $this;
    }

	/**
     * @see \Zend\Code\Annotation\Parser\ParserInterface::registerAnnotations()
     */
    public function registerAnnotations($annotations)
    {
        if (!is_array($annotations) && !($annotations instanceof \Traversable)) {
            throw new exception\InvalidArgumentException(sprintf(
                '%s: expects an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($annotations) ? get_class($annotations) : gettype($annotations))
            ));
        }

        foreach ($annotations as $annotation) {
            $this->registerAnnotation($annotation);
        }

        return $this;
    }
}
