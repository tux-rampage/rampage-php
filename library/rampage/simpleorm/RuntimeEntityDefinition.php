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
class RuntimeEntityDefintion implements EntityDefinitionInterface
{
    private $entities = array();

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
     * @param string $class
     * @return \rampage\simpleorm\ReflectionEntityDefintion
     */
    protected function processClass($class)
    {
        if (isset($this->entities[$class]) || !class_exists($class)) {
            return $this;
        }

        $reflection = new ClassReflection($class);
        $annotations = $reflection->getAnnotations($this->annotationManager);

        $this->entities[$class]['repository'] = false;

        foreach ($annotations as $annotation) {
            if ($annotation instanceof annotations\EntityAnnotation) {
                $this->entities[$class]['repository'] = $annotation->getRepositoryName();
                break;
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

        if (isset($this->entities[$entity]['repository'])) {
            return $this->entities[$entity]['repository'];
        }

        return false;
    }
}