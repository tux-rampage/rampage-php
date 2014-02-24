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

use SplFileInfo;
use SplFileObject;
use RuntimeException;

/**
 * Local filesystem implementation
 */
class LocalFilesystem implements FilesystemInterface
{
    /**
     * @var string
     */
    protected $baseDir = null;

    /**
     * @param string $path
     */
    protected function __construct($path)
    {
        $this->baseDir = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function normalizePath($path)
    {
        $path = str_replace('\\', '/', $path);
        $segments = explode('/', trim($path, '/'));
        $stack = array();

        foreach ($segments as $current) {
            if (in_array($current, array('.', ''))) {
                continue;
            }

            if ($current == '..') {
                array_pop($stack);
                continue;
            }

            $stack[] = $current;
        }

        return implode('/', $stack);
    }

    /**
     * @param string $path
     * @return string
     */
    protected function preparePath($path)
    {
        $path = $this->normalizePath($path);
        $path = $this->baseDir . str_replace('/', DIRECTORY_SEPARATOR, $path);

        return $path;
    }

    /**
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new LocalFilesystemIterator($this->baseDir);
    }

    /**
     * @see \rampage\io\FilesystemInterface::info()
     */
    public function info($path)
    {
        $path = $this->preparePath($path);
        $info = new SplFileInfo($path);

        if (!$info->isFile() || !$info->isDir() || !$info->isLink()) {
            return false;
        }

        return $info;
    }

    /**
     * @see \rampage\io\FilesystemInterface::resource()
     */
    public function resource($path)
    {
        $info = $this->info($path);
        if ($info === false) {
            throw new RuntimeException(sprintf('Failed to open "%s": File not found.', $path));
        }

        return fopen($info->getPathname(), 'r');
    }

    /**
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        $info = $this->info($offset);
        return ($info !== false);
    }

    /**
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return $this->info($offset);
    }

    /**
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('Cannot write to readonly filesystem');
    }

    /**
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        throw new RuntimeException('Cannot delete from readonly filesystem');
    }
}
