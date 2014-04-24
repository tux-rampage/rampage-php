<?php
/**
 * This is part of @application_name@
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
 * @package   @package_name@
 * @author    Axel Helmert
 * @copyright Copyright (c) 2012 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core;

/**
 * Utilities class
 */
class Utils
{
    /**
     * camelized names
     *
     * @var array
     */
    private static $camelizeCache = array();

    /**
     * underscored names
     *
     * @var array
     */
    private static $underscoreCache = array();

    /**
     * Camelize the given name
     *
     * @param string $name
     * @return string
     */
    public static function camelize($name)
    {
        if (!isset(self::$camelizeCache[$name])) {
            $camelizedName = ucwords(str_replace('_', ' ', $name));
            $camelizedName = str_replace(' ', '', $camelizedName);

            self::$camelizeCache[$name] = $camelizedName;
            if (!isset(self::$underscoreCache[$camelizedName])) {
                self::$underscoreCache[$camelizedName] = $name;
            }
        }

        return self::$camelizeCache[$name];
    }

    /**
     * Convert a camelized name to underscored name
     *
     * @param string $name
     * @return string
     */
    public static function underscore($name)
    {
        if (!isset(self::$underscoreCache[$name])) {
            $underscoreName = strtolower(preg_replace('/(\B)([A-Z])/', '$1_$2', $name));
            self::$underscoreCache[$name] = $underscoreName;

            if (!isset(self::$camelizeCache[$underscoreName])) {
                self::$camelizeCache[$underscoreName] = $name;
            }
        }

        return self::$underscoreCache[$name];
    }

    /**
     * Resolve realpath from streams
     *
     * @param string $path
     */
    public static function realpath($path)
    {
        // check for stream uri
        if (preg_match('~^[a-z0-9-_.]+://~', $path)) {
            $stats = (file_exists($path))? stat($path) : false;
            if ($stats && isset($stats['realpath'])) {
                return $stats['realpath'];
            }

            return ($stats !== false) ? $path : false;
        }

        return realpath($path);
    }

    /**
     * Set toptions to object
     *
     * @param string $object
     * @param array|Traversable $options
     */
    public static function setOptions($object, $options)
    {
        if (!is_array($options) || ($options instanceof \Traversable)) {
            throw new exception\InvalidArgumentException('$options must be an array or implement the Traversable interface.');
        }

        foreach ($options as $key => $value) {
            $method = 'set' . static::camelize($key);
            if (!is_callable(array($object, $method))) {
                continue;
            }

            $object->$method($value);
        }
    }

    /**
     * returns the printable class name
     *
     * @param string $class
     * @return string
     */
    public static function getPrintableClassName($class)
    {
        return strtr($class, '\\', '.');
    }

    /**
     * Format type name
     *
     * @param mixed $var
     * @return string
     */
    public static function getPrintableTypeName($var)
    {
        if (is_object($var)) {
            return static::getPrintableClassName(get_class($var));
        }

        if (is_resource($var)) {
            return '[' . get_resource_type($var) . ' resource]';
        }

        return gettype($var);
    }

    /**
     * @param mixed $var
     * @return boolean
     */
    public static function isTraversable($var)
    {
        return (is_array($var) || ($var instanceof \Traversable));
    }

    /**
     * @param mixed $var
     * @return string
     */
    public static function varExport($var)
    {
        $php = '';

        if (is_array($var)) {
            $items = array();
            foreach ($var as $k => $v) {
                $items[] = var_export($k, true) . ' => ' . static::varExport($var);
            }

            return 'array(' . implode($items, ', ') . ')';
        }

        if (is_object($var) && ($var instanceof ExportableClassInterface)) {
            $code = $var->exportAsPhpCode();
            if (!is_string($code)) {
                $code = get_class($var) . '::__set_state(' . var_export($code) . ')';
            }

            return $code;
        }

        return var_export($var, true);
    }
}
