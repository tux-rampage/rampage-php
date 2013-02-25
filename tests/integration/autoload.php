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

// Include path
set_include_path(
	__DIR__ . PATH_SEPARATOR .
	dirname(dirname(__DIR__)) . '/library' . PATH_SEPARATOR .
	get_include_path()
);

// PSR-0 autoloader
spl_autoload_register(function($class) {
	$file = str_replace(array('_', '\\'), array('/', '/'), $class) . '.php';
    $file = ltrim($file, '/');
    $path = stream_resolve_include_path($file);

    if ($path) {
        include $path;
    }
});