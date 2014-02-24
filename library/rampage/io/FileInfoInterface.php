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

/**
 * Fileinfo
 */
interface FileInfoInterface
{
    /**
     * Returns the filename without path information
     *
     * @return string
     */
    public function getFilename();

    /**
     * Returns the relative path
     */
    public function getRelativePath();

    /**
     * Returns the filesystem instance
     *
     * @return FilesystemInterface
     */
    public function getFilesystem();

    /**
     * Returns the timestamp the file was last modified
     *
     * @return int
     */
    public function getMTime();

    /**
     * Returns the timestamp the file was created
     *
     * @return int
     */
    public function getCTime();

    /**
     * Returns the file size in bytes
     *
     * @return int
     */
    public function getSize();

    /**
     * @return bool
     */
    public function isFile();

    /**
     * @return bool
     */
    public function isDir();

    /**
     * @return bool
     */
    public function exists();

    /**
     * @return bool
     */
    public function isReadable();

    /**
     * @return bool
     */
    public function isWritable();

    /**
     * Open the file
     *
     * @param string $mode Open mode as accepted by fopen
     * @return \SplFileObject
     */
    public function open($mode = null);

    /**
     * Returns the stream resource to this file
     *
     * @param string $mode Open mode as accepted by fopen
     * @return resource The resource on success, false on error
     */
    public function resource($mode = null);

}