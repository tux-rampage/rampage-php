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
    private $classAnnotationManager = null;

    /**
     * Reflection instances
     *
     * @var array
     */
    protected $reflections = array();

    /**
     * @param \Zend\Code\Annotation\AnnotationManager $classAnnotationManager
     */
    public function __construct(AnnotationManager $classAnnotationManager = null)
    {
        $this->classAnnotationManager = $classAnnotationManager? : new annotation\ClassAnnotationManager();
    }

    /**
     * @param string $class
     * @return boolean|ClassReflection
     */
    protected function reflect($class)
    {
        $class = $this->formatClassName($class);
        if (!class_exists($class)) {
            return false;
        }

        if (!isset($this->reflections[$class])) {
            $this->reflections[$class] = new ClassReflection($class);
        }

        return $this->reflections[$class];
    }

    /**
     * @param ClassReflection $class
     * @return \rampage\simpleorm\metadata\annotation\EntityAnnotation|false
     */
    protected function getEntityAnnotation($reflection)
    {
        while ($reflection instanceof ClassReflection) {
            foreach ($reflection->getAnnotations($this->getClassAnnotationManager()) as $annotation) {
                if ($annotation instanceof annotation\EntityAnnotation) {
                    return $annotation;
                }
            }

            $reflection = $reflection->getParentClass();
            if (!$reflection) {
                break;
            }

            if (isset($this->reflections[$reflection->getName()])) {
                $reflection = $this->reflections[$reflection->getName()];
            } else {
                $this->reflections[$reflection->getName()] = $reflection;
            }
        }

        return false;
    }

    /**
     * @param string $class
     */
    protected function isEntity($class)
    {
        return ($this->getEntityAnnotation($this->reflect($class)) !== false);
    }

    /**
     * @return \Zend\Code\Annotation\AnnotationManager
     */
    public function getClassAnnotationManager()
    {
        return $this->classAnnotationManager;
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
        return $this->isEntity($name);
    }

    /**
     * @see \rampage\simpleorm\metadata\DriverInterface::loadEntityDefintion()
     */
    public function loadEntityDefintion($name, Metadata $metadata, Entity $entity = null)
    {
        $reflection = $this->reflect($name);
        $entityAnnotation = $this->getEntityAnnotation($reflection);

        if (!$entityAnnotation) {
            throw new exception\InvalidArgumentException('Invalid entity: ' . $name);
        }

        if ($entity === null) {
            $entity = new Entity($name, $entityAnnotation->getTable());
        }

        $annotations = $reflection->getAnnotations($this->getClassAnnotationManager());
        foreach ($annotations as $annotation) {
            if ($annotation instanceof annotation\FieldAnnotation) {
                $name = $annotation->getProperty();
                if (!$name) {
                    continue;
                }

                $attribute = new Attribute($name, $annotation->getField(), $annotation->getType());
                $entity->getAttributes()->add($attribute);

                continue;
            }


        }
    }
}