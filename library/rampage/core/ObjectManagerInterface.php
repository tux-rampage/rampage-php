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

namespace rampage\core;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Object manager interface
 */
interface ObjectManagerInterface extends ServiceLocatorInterface
{
    /**
     * Add aliases
     *
     * @param array|\Traversable $config
     * @throws \rampage\core\exception\InvalidArgumentException
     */
    public function addAliases($config);

    /**
     * Resolve a class name
     *
     * @param string $class
     * @return string
     */
    public function resolveClassName($name);

//     /**
//      * Extended service locator interface to allow parameters and disable initializers
//      *
//      * @param string $name
//      * @param array $params
//      * @param bool $callInitializers
//      * @return object
//      */
//     public function get($name, array $params = array(), $callInitializers = true);

    /**
     * Retrieve a new instance of a class
     *
     * Forces retrieval of a discrete instance of the given class, using the
     * constructor parameters provided.
     *
     * @param  mixed       $name   Class name or service alias
     * @param  array       $params Parameters to pass to the constructor
     * @return object|null
     */
    public function newInstance($name, array $params = array());

    /**
     * Check if a service can be created
     *
     * @param string $name
     * @return bool
     */
    public function canCreate($name);
}