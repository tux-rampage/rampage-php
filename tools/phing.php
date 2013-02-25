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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

/**
 * Custom phing initializer
 */
class RampagePhing
{
    /**
     * Initialize
     */
    public static function init()
    {
        // Add LUKA phing class path
        set_include_path(dirname(__DIR__) . '/library' . PATH_SEPARATOR . get_include_path());
        if (getenv('PHP_CLASSPATH')) {
            if (!defined('PHP_CLASSPATH')) {
                define('PHP_CLASSPATH',  getenv('PHP_CLASSPATH') . PATH_SEPARATOR . get_include_path());
            }
            ini_set('include_path', PHP_CLASSPATH);
        } else {
            if (!defined('PHP_CLASSPATH')) {
                define('PHP_CLASSPATH',  get_include_path());
            }
        }
    }

    /**
     * Auto loader
     *
     * @param string $class
     */
    public static function autoload($class)
    {
        $file = strtr($class, array('\\' => '/', '_' => '/')) . '.php';
        $file = trim($file, '/');
        $path = stream_resolve_include_path($file);

        if (!$path) {
            return false;
        }

        include $path;
        return class_exists($class, false);
    }

    /**
     * Invoke Phing
     *
     * @param array $args
     */
    public static function run($args)
    {
        if (!is_array($args)) {
            $args = array();
        }

        array_unshift($args, 'rampage\phing\InitListener');
        array_unshift($args, '-listener');

        Phing::fire($args);
    }
}


require_once 'phing/Phing.php';

try {
    spl_autoload_register(array('RampagePhing', 'autoload'));

    /* Setup Phing environment */
    RampagePhing::init();
    Phing::startup();

    // Set phing.home property to the value from environment
    // (this may be NULL, but that's not a big problem.)
    Phing::setProperty('phing.home', getenv('PHING_HOME'));

    // Grab and clean up the CLI arguments
    $args = isset($argv) ? $argv : $_SERVER['argv']; // $_SERVER['argv'] seems to not work (sometimes?) when argv is registered
    array_shift($args); // 1st arg is script name, so drop it

    // Invoke the commandline entry point
    RampagePhing::run($args);

    // Invoke any shutdown routines.
    Phing::shutdown();

} catch (ConfigurationException $e) {
    Phing::printMessage($e);
    exit(2); // This was convention previously for configuration errors.
} catch (Exception $e) {
    // Assume the message was already printed as part of the build and
    // exit with non-0 error code.
    $exitCode = (int)$e->getCode();
    if ($exitCode < 1) {
        $exitCode = 1;
    }

    exit($exitCode);
}
