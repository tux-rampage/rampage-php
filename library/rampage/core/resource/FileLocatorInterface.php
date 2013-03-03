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

namespace rampage\core\resource;

/**
 * file locator interface
 */
interface FileLocatorInterface
{
    /**
     * Ensure the file is published
     *
     * Note: This files are always resolved with the type 'public'
     *
     * @param string $file
     * @param string $scope
     * @return string The published path relative to the media directory
     */
    public function publish($file, $scope = null);

    /**
     * Resolve a file path
     *
     * @param string $type
     * @param string $file
     * @param string $scope
     * @param bool $asFileInfo
     * @return string|\SplFileInfo|false
     */
    public function resolve($type, $file, $scope = null, $asFileInfo = false);
}