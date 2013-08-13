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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm;

use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Annotation\AnnotationManager;

/**
 * Runtime entity definition
 */
class EntityRuntimeDefintion extends EntityArrayDefinition
{
    /**
     * @var AnnotationManager
     */
    private $annotationManager = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->annotationManager = new AnnotationManager();
        $this->annotationManager->attach(new annotations\ClassAnnotationParser());
    }

    /**
     * @param ClassReflection $reflection
     * @param array $classes
     */
    private function processClassAnnotations(ClassReflection $reflection, array $classes)
    {
        $class = $reflection->getName();
        if (!isset($this->data[$class])) {
            $annotations = $reflection->getAnnotations($this->annotationManager);

            $this->data[$class]['repository'] = false;

            foreach ($annotations as $annotation) {
                if ($annotation instanceof annotations\EntityAnnotation) {
                    $this->data[$class]['repository'] = $annotation->getRepositoryName();
                    break;
                }
            }
        }


        foreach ($classes as $child) {
            if (!isset($this->data[$child]['repository']) || !$this->data[$child]['repository']) {
                $this->data[$child]['repository'] = $this->data[$class]['repository'];
            }
        }
    }

    /**
     * @param string $class
     * @return \rampage\simpleorm\ReflectionEntityDefintion
     */
    protected function processClass($class)
    {
        if (isset($this->data[$class]) || !class_exists($class)) {
            return $this;
        }

        $reflection = new ClassReflection($class);
        $this->processClassAnnotations($reflection, array());

        if (!$this->data[$class]['repository']) {
            $parent = $reflection->getParentClass();
            $classes = array($class);

            while (!$this->data[$class]['repository'] && $parent) {
                $this->processClassAnnotations($parent, $classes);
                $classes[] = $parent->getName();
                $parent = $parent->getParentClass();
            }
        }

        if (!$this->data[$class]['repository']) {
            $interfaces = $reflection->getInterfaces();
            while (!$this->data[$class]['repository'] && ($interface = array_shift($interfaces))) {
                $this->processClassAnnotations($interface, $classes);
                $classes[] = $interface->getName();
            }
        }

        return $this;
    }

    /**
     * @see \rampage\simpleorm\EntityDefinitionInterface::getRepositoryName()
     */
    public function getRepositoryName($entity)
    {
        $this->processClass($entity);
        return parent::getRepositoryName($entity);
    }
}
