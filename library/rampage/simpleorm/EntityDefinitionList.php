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

/**
 * Entity definition list
 */
class EntityDefinitionList implements EntityDefinitionInterface
{
    /**
     * @var array
     */
    private $definitions = array();

    /**
     * @param array|Traversable|EntityDefinitionInterface $definitions
     */
    public function __construct($definitions = null)
    {
        if ($definitions !== null) {
            if (!is_array($definitions) && !($definitions instanceof \Traversable)) {
                $definitions = array($definitions);
            }

            $this->addDefinitions($definitions);
        }

        $this->addDefinition(new EntityRuntimeDefintion());
    }

    /**
     * @param EntityDefinitionInterface $definition
     * @return \rampage\simpleorm\EntityDefinitionList
     */
    public function addDefinition(EntityDefinitionInterface $definition, $topOfStack = false)
    {
        $class = get_class($definition);

        if ($topOfStack) {
            unset($this->definitions[$class]);
            $this->definitions = array_splice($this->definitions, 0, 0, array($class => $definition));
        } else {
            $this->definitions[$class] = $definition;
        }

        return $this;
    }

    /**
     * @param array|Traversable $definitions
     * @return self
     */
    public function addDefinitions($definitions)
    {
        foreach ($definitions as $definition) {
            $this->addDefinition($definition);
        }

        return $this;
    }

    /**
     * @param EntityDefinitionInterface $definition
     * @return \rampage\simpleorm\EntityDefinitionList
     */
    public function removeDefinition(EntityDefinitionInterface $definition)
    {
        $class = get_class($definition);
        unset($this->definitions[$class]);

        return $this;
    }

    /**
     * @param string $class
     * @return \rampage\simpleorm\EntityDefinitionInterface
     */
    public function getDefinition($class)
    {
        if (!isset($this->definitions[$class])) {
            return null;
        }

        return $this->definitions[$class];
    }

    /**
     * @return array
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * @see \rampage\simpleorm\EntityDefinitionInterface::hasRepository()
     */
    public function hasRepository($entity)
    {
        foreach ($this->definitions as $definition) {
            if ($definition->hasRepository($entity)) {
                return true;
            }
        }

        return false;
    }

	/**
     * @see \rampage\simpleorm\EntityDefinitionInterface::getRepositoryName()
     */
    public function getRepositoryName($entity)
    {
        foreach ($this->definitions as $definition) {
            if ($name = $definition->getRepositoryName($entity)) {
                return $name;
            }
        }

        return false;
    }
}
