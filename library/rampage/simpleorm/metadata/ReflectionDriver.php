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

use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Annotation\AnnotationManager;

/**
 * Runtime definition
 */
class ReflectionDriver implements DriverInterface
{
    /**
     * @var \Zend\Code\Annotation\AnnotationManager
     */
    private $annotationManager = null;

    /**
     * @param \Zend\Code\Annotation\AnnotationManager $annotationManager
     */
    public function __construct(AnnotationManager $annotationManager = null)
    {
        if (!$annotationManager) {
            $annotationManager = new AnnotationManager();
            $annotationManager->attach();
        }

        $this->annotationManager = $annotationManager;
    }

    /**
     * @return \Zend\Code\Annotation\AnnotationManager
     */
    public function getAnnotationManager()
    {
        return $this->annotationManager;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function formatClassName($name)
    {
        return strtr($name, '.', '\\');
    }

    /**
     * @see \rampage\simpleorm\metadata\DriverInterface::hasEntityDefintion()
     */
    public function hasEntityDefintion($name)
    {
        $class = $this->formatClassName($name);
        return class_exists($class);
    }

    /**
     * @see \rampage\simpleorm\metadata\DriverInterface::loadEntityDefintion()
     */
    public function loadEntityDefintion($name, Metadata $metadata, Entity $entity = null)
    {
        $class = $this->formatClassName($name);
        $reflection = new ClassReflection($class);

        $annotations = $reflection->getAnnotations(new annotation\ClassAnnotationManager());
    }
}