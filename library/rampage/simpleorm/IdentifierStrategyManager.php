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

namespace rampage\simpleorm;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ID strategy manager
 */
class IdentifierStrategyManager implements ServiceLocatorInterface
{
    /**
     * @var array
     */
    protected $classes = array();

    /**
     * Construct
     */
    public function __construct()
    {
        $classes = array(
            'AutoIncrementStrategy' => 'rampage\simpleorm\AutoincrementIdentifierStrategy',
            'AutoIncrementIdentifierStrategy' => 'rampage\simpleorm\AutoincrementIdentifierStrategy',
            'StaticIdentifierStrategy' => 'rampage\simpleorm\AutoincrementIdentifierStrategy',
        );

        foreach ($classes as $name => $className) {
            $this->addClass($name, $className);
        }
    }

    public function addClass($name, $className)
    {
        $name = strtolower($className);
        $this->classes[$name] = strtr($className, '.', '\\');
    }

    /**
     * @see \Zend\ServiceManager\ServiceLocatorInterface::has()
     */
    public function has($name)
    {
        $cName = strtolower($name);
        $class = strtr($name, '.', '\\');
        $exists = isset($this->classes[$cName]) || (class_exists($class));

        return $exists;
    }

    /**
     * @see \Zend\ServiceManager\ServiceLocatorInterface::get()
     */
    public function get($name, array $options = array())
    {
        $cName = strtolower($name);
        $class = (isset($this->classes[$cName]))? $this->classes[$cName] : strtr($name, '.', '\\');
        $options = array_values($options);

        // try to avoid reflection for performance
        switch (count($options)) {
            case 0:
                return new $class();

            case 1:
                return new $class($options[0]);

            case 2:
                return new $class($options[0], $options[1]);

            case 3:
                return new $class($options[0], $options[1], $options[2]);

            case 4:
                return new $class($options[0], $options[1], $options[2], $options[3]);
        }

        $reflection = new \ReflectionClass($class);
        return $reflection->newInstanceArgs($options);
    }
}
