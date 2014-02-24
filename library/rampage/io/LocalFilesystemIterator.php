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

use RecursiveIterator;
use FilesystemIterator;

/**
 * Local FS iterator
 */
class LocalFilesystemIterator implements RecursiveIterator
{
    /**
     * @var string
     */
    protected $path = null;

    /**
     * @var string
     */
    protected $baseDir = null;

    /**
     * @var FilesystemIterator
     */
    protected $innerIterator = null;

    /**
     * @param LocalFilesystem $filesystem
     * @param string $path
     */
    public function __construct($baseDir, $path = '')
    {
        $this->baseDir = rtrim($baseDir, '/');
        $this->path = trim($path, '/');

        $this->innerIterator = new FilesystemIterator($baseDir . '/' . $path);
    }

    /**
     * @return boolean
     */
    protected function accept()
    {
        return in_array($this->current()->getFilename(), array('.', '..'));
    }

    /**
     * @see RecursiveIterator::current()
     * @return \SplFileInfo
     */
    public function current()
    {
        return $this->innerIterator->current();
    }

    /**
     * @see RecursiveIterator::getChildren()
     */
    public function getChildren()
    {
        if (!$this->hasChildren()) {
            return null;
        }

        return new self($this->baseDir, $this->path . '/' . $this->current()->getFilename());
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

        return $this->path . '/' . $this->current()->getFilename();
    }

    /**
     * @see RecursiveIterator::next()
     */
    public function next()
    {
        do {
            $this->innerIterator->next();
        } while ($this->valid() && !$this->accept());

        return $this;
    }

    /**
     * @see RecursiveIterator::rewind()
     */
    public function rewind()
    {
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
}
