<?php
/**
 * This is part of rampage-php
 * Copyright (c) 2014 Axel Helmert
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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\io;

use ArrayAccess;
use RecursiveIterator;

/**
 * Filesystem interface
 */
interface FilesystemInterface extends ArrayAccess, RecursiveIterator
{
    /**
     * Returns a stream resource for the given path
     *
     * @param string $path
     * @param string $flags The flags accepted by fopen
     * @return resource|bool The stream resource or false
     */
    public function resource($path, $flags);

    /**
     * Open the given file for read/write access
     *
     * @param string $path Relative filepath to open
     * @param string $flags The flags accepted by fopen
     * @return \SplFileObject
     */
    public function open($path, $flags);

    /**
     * @param string $path Relative path to the file/directory to open
     * @return \SplFileInfo
     */
    public function info($path);
}