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

class WrappedFileInfo implements FileInfoInterface
{
    /**
     * @var \SplFileInfo
     */
    protected $wrapped = null;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem = null;

    /**
     * @var string
     */
    protected $path = null;

    /**
     * @param string $path
     * @param FilesystemInterface $filesystem
     * @param SplFileInfo $wrapped
     */
    protected function __construct($path, FilesystemInterface $filesystem, SplFileInfo $wrapped)
    {
        $this->path = $path;
        $this->filesystem = $filesystem;
        $this->wrapped = $wrapped;
    }

    /**
     * @see \rampage\io\FileInfoInterface::exists()
     */
    public function exists()
    {
        return $this->isFile() || $this->isDir();
    }

    /**
     * @see \rampage\io\FileInfoInterface::getCTime()
     */
    public function getCTime()
    {
        return $this->wrapped->getCTime();
    }

    /**
     * @see \rampage\io\FileInfoInterface::getFilename()
     */
    public function getFilename()
    {
        return $this->wrapped->getFilename();
    }

    /**
     * @see \rampage\io\FileInfoInterface::getFilesystem()
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @see \rampage\io\FileInfoInterface::getMTime()
     */
    public function getMTime()
    {
        return $this->wrapped->getMTime();
    }

    /**
     * @see \rampage\io\FileInfoInterface::getPathname()
     */
    public function getPathname()
    {
        return $this->wrapped->getPathname();
    }


    /**
     * @see \rampage\io\FileInfoInterface::getRelativePath()
     */
    public function getRelativePath()
    {
        return $this->path;
    }

    /**
     * @see \rampage\io\FileInfoInterface::getSize()
     */
    public function getSize()
    {
        return $this->wrapped->getSize();
    }

    /**
     * @see \rampage\io\FileInfoInterface::isDir()
     */
    public function isDir()
    {
        return $this->wrapped->isDir();
    }

    /**
     * @see \rampage\io\FileInfoInterface::isFile()
     */
    public function isFile()
    {
        return $this->wrapped->isFile() || $this->wrapped->isLink();
    }

    /**
     * @return boolean
     */
    protected function isReadonly()
    {
        return !($this->filesystem instanceof WritableFilesystemInterface);
    }

    /**
     * @see \rampage\io\FileInfoInterface::isReadable()
     */
    public function isReadable()
    {
        return $this->wrapped->isReadable();
    }

    /**
     * @see \rampage\io\FileInfoInterface::isWritable()
     */
    public function isWritable()
    {
        $result = (!$this->isReadonly() && $this->wrapped->isWritable());
        return $result;
    }

    /**
     * @see \rampage\io\FileInfoInterface::open()
     */
    public function open($mode = null)
    {
        $mode = $mode? : 'r';

        if ($this->isReadonly() && ($mode != 'r')) {
            throw new \InvalidArgumentException('Bad open mode for read-only filesystem: "%s"');
        }

        return new FileObject($this->filesystem, $this->getRelativePath(), $this->getPathname(), $mode);
    }

    /**
     * @see \rampage\io\FileInfoInterface::resource()
     */
    public function resource($mode = null)
    {
        $mode = $mode? : 'r';

        if ($this->isReadonly() && ($mode != 'r')) {
            throw new \InvalidArgumentException('Bad open mode for read-only filesystem: "%s"');
        }

        return fopen($this->wrapped->getPathname(), 'r');
    }
}
