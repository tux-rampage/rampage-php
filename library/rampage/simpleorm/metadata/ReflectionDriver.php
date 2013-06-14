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
use rampage\simpleorm\IdentifierStrategyManager;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Reflection\PropertyReflection;

/**
 * Runtime definition
 */
class ReflectionDriver implements DriverInterface
{
    const FIELD_ANNOTATION = 'rampage\simpleorm\metadata\annotation\FieldAnnotation';
    const IDENTIFIER_ANNOTATION = 'rampage\simpleorm\metadata\annotation\IdentifierAnnotation';

    /**
     * @var IdentifierStrategyManager
     */
    private $identifierStrategies = null;

    /**
     * @var \Zend\Code\Annotation\AnnotationManager
     */
    private $classAnnotationManager = null;

    /**
     * @var \Zend\Code\Annotation\AnnotationManager
     */
    private $propertyAnnotationManager = null;

    /**
     * Reflection instances
     *
     * @var array
     */
    protected $reflections = array();

    /**
     * @param \Zend\Code\Annotation\AnnotationManager $classAnnotationManager
     */
    public function __construct(
        ServiceLocatorInterface $identifierStrategies = null,
        AnnotationManager $classAnnotationManager = null,
        AnnotationManager $propertyAnnotationManager = null)
    {
        $this->identifierStrategies = $identifierStrategies? : new IdentifierStrategyManager();
        $this->classAnnotationManager = $classAnnotationManager? : new annotation\ClassAnnotationManager();
        $this->propertyAnnotationManager = $propertyAnnotationManager? : new annotation\PropertyAnnotationManager();
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
        $origReflection = $reflection;

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

        // Analyze interface annotations
        foreach (array_reverse($origReflection->getInterfaceNames()) as $interface) {
            $reflection = $this->reflect($interface);

            foreach ($reflection->getAnnotations($this->getClassAnnotationManager()) as $annotation) {
                if ($annotation instanceof annotation\EntityAnnotation) {
                    return $annotation;
                }
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
     * @return \Zend\Code\Annotation\AnnotationManager
     */
    public function getPropertyAnnotationManager()
    {
        return $this->propertyAnnotationManager;
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
     * @param IdentifierStrategyAnnotation $annotation
     */
    protected function createIdentifierStrategy(annotation\IdentifierStrategyAnnotation $annotation)
    {
        /* @var $annotation annotation\IdentifierStrategyAnnotation */
        return $this->identifierStrategies->get($annotation->getClass(), $annotation->getOptions());
    }

    /**
     * @param string $name
     * @param Entity $entity
     * @param FieldAnnotation $annotation
     */
    private function processFieldAnnotation($name, Entity $entity, annotation\FieldAnnotation $annotation)
    {
        // Don't overwrite existing/defined attributes
        if ($entity->hasAttribute($name)) {
            return $this;
        }

        $attribute = new Attribute($name, $annotation->getField(), $annotation->getType());
        $entity->getAttributes()->add($attribute);
        return $this;
    }

    /**
     * @param ClassReflection $reflection
     * @param Entity $entity
     */
    private function processAnnotations(ClassReflection $reflection, Entity $entity, &$hasIdentifier)
    {
        $annotations = new annotation\AnnotationIterator($reflection->getAnnotations($this->getClassAnnotationManager()));

        foreach ($annotations->setTypeFilter(static::FIELD_ANNOTATION) as $annotation) {
            $name = $annotation->getProperty();

            if ($name) {
                $this->processFieldAnnotation($name, $annotation);
            }
        }

        if (!$hasIdentifier) {
            $identifierStrategy = null;
            $processedIdentifier = false;

            /* @var $annotation annotation\IdentifierAnnotation */
            foreach ($annotations->setTypeFilter(static::IDENTIFIER_ANNOTATION) as $annotation) {
                if (!$entity->hasAttribute($annotation->getAttribute())) {
                    continue;
                }

                $entity->getAttribute($annotation->getAttribute())
                    ->setIsIdentifier(true)
                    ->setIsAutoIncrement($annotation->isAutoIncrement());

                if (!$identifierStrategy && $annotation->getStrategy()->getClass()) {
                    $identifierStrategy = $this->createIdentifierStrategy($annotation->getStrategy());
                }

                $processedIdentifier = true;
            }

            $hasIdentifier = $processedIdentifier;
            if ($identifierStrategy) {
                $entity->getIdentifier()->setStrategy($identifierStrategy);
            }
        }

        return $this;

    }

    /**
     * @param ClassReflection $reflection
     * @param Entity $entity
     */
    private function processPropertyAnnotations(ClassReflection $reflection, Entity $entity, &$hasIdentifier)
    {
        $filter = PropertyReflection::IS_PRIVATE | PropertyReflection::IS_PROTECTED | PropertyReflection::IS_PUBLIC;
        $identifierStrategy = null;
        $processedIdentifier = $hasIdentifier;

        /* @var $property PropertyReflection */
        foreach ($reflection->getProperties($filter) as $property) {
            $name = $property->getName();
            $annotations = new annotation\AnnotationIterator($property->getAnnotations($this->getPropertyAnnotationManager()));

            foreach ($annotations->setTypeFilter(static::FIELD_ANNOTATION) as $annotation) {
                $this->processFieldAnnotation($property->getName(), $entity, $annotation);
                break;
            }

            if (!$hasIdentifier) {
                foreach ($annotations->setTypeFilter(static::IDENTIFIER_ANNOTATION) as $annotation) {
                    /* @var $annotation instanceof annotation\IdentifierAnnotation */
                    $entity->getAttribute($property->getName())->setIsIdentifier(true);

                    if (!$identifierStrategy && $annotation->getStrategy()->getClass()) {
                        $identifierStrategy = $this->createIdentifierStrategy($annotation->getStrategy());
                    }

                    $processedIdentifier = true;
                }
            }
        }

        $hasIdentifier = $processedIdentifier;
        if ($identifierStrategy) {
            $entity->getIdentifier()->setStrategy($identifierStrategy);
        }

        return $this;
    }

    /**
     * @see \rampage\simpleorm\metadata\DriverInterface::loadEntityDefintion()
     */
    public function loadEntityDefintion($name, Metadata $metadata, Entity $entity = null)
    {
        $reflection = $this->reflect($name);
        $entityAnnotation = $this->getEntityAnnotation($reflection);
        $hasIdentifier = false;

        if (!$entityAnnotation) {
            throw new exception\InvalidArgumentException('Invalid entity: ' . $name);
        }

        if ($entity === null) {
            $entity = new Entity($name, $entityAnnotation->getTable());
        }

        while ($reflection instanceof ClassReflection) {
            $this->processPropertyAnnotations($reflection, $entity, $hasIdentifier)
                ->processAnnotations($reflection, $entity, $hasIdentifier);

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

        return $this;
    }
}