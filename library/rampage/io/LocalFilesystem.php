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
use FilesystemIterator;
use RuntimeException;
use LogicException;

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
     * Current path for iterator
     */
    protected $path = null;

    /**
     * @var FileInfoInterface
     */
    protected $current = null;

    /**
     * @var FilesystemIterator
     */
    protected $innerIterator = null;

    /**
     * @param string $path
     */
    protected function __construct($baseDir)
    {
        $this->baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->innerIterator = new FilesystemIterator($this->baseDir);

        $this->rewind();
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
     * @see \rampage\io\FilesystemInterface::info()
     */
    public function info($path)
    {
        $normalized = $this->normalizePath($path);
        $info = new SplFileInfo($this->preparePath($path));

        return new WrappedFileInfo($normalized, $this, $info);
    }

    /**
     * @return boolean
     */
    protected function accept()
    {
        return in_array($this->innerIterator->current()->getFilename(), array('.', '..'));
    }

    /**
     * @see RecursiveIterator::current()
     * @return FileInfoInterface
     */
    public function current()
    {
        if (!$this->current && $this->valid()) {
            $info = $this->innerIterator->current();
            $path = $this->normalizePath($this->path . '/' . $info->getFilename());

            return new WrappedFileInfo($path, $this, $info);
        }

        return $this->current;
    }

    /**
     * @see RecursiveIterator::getChildren()
     */
    public function getChildren()
    {
        if (!$this->hasChildren()) {
            return null;
        }

        $children = clone $this;

        $children->path = $this->current()->getRelativePath();
        $children->innerIterator = new FilesystemIterator($this->preparePath($children->path));
        $children->rewind();

        return $children;
    }

    /**
     * @see RecursiveIterator::hasChildren()
     */
    public function hasChildren()
    {
        $hasChildren = ($this->valid() && $this->current()->isDir());
        return $hasChildren;
    }

    /**
     * @see RecursiveIterator::key()
     */
    public function key()
    {
        if (!$this->valid()) {
            return false;
        }

        return $this->current()->getRelativePath();
    }

    /**
     * @see RecursiveIterator::next()
     */
    public function next()
    {
        do {
            $this->current = null;
            $this->innerIterator->next();
        } while ($this->valid() && !$this->accept());

        return $this;
    }

    /**
     * @see RecursiveIterator::rewind()
     */
    public function rewind()
    {
        $this->current = null;
        $this->innerIterator->rewind();

        while ($this->valid() && !$this->accept()) {
            $this->innerIterator->next();
        }

        return $this;
    }

    /**
     * @see RecursiveIterator::valid()
     */
    public function valid()
    {
        return $this->innerIterator->valid();
    }

    /**
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        $info = $this->info($offset);
        return $info->exists();
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
        throw new LogicException('Cannot write to readonly filesystem');
    }

    /**
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        throw new LogicException('Cannot delete from readonly filesystem');
    }
}
