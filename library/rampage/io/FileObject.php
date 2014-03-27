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

use SplFileObject;

class FileObject extends SplFileObject implements FileInfoInterface
{
    const CLASSNAME = __CLASS__;

    /**
     * @var unknown
     */
    protected $relativePath = null;

    protected $filesystem = null;

    protected $openMode = null;

    /**
     * @param FilesystemInterface $filesystem
     * @param string $relativePath
     * @param string $path
     * @param string $mode
     */
    public function __construct(FilesystemInterface $filesystem, $relativePath, $path, $mode)
    {
        $this->filesystem = $filesystem;
        $this->relativePath = $relativePath;
        $this->openMode = $mode;

        parent::__construct($path, $mode);
    }

    /**
     * @return boolean
     */
    public function isReadonly()
    {
        return !($this->filesystem instanceof WritableFilesystemInterface);
    }

    /**
     * {@inheritdoc}
     * @see SplFileObject::isWritable()
     */
    public function isWritable()
    {
        return !$this->isReadonly() && parent::isWritable();
    }

	/**
     * @see \rampage\io\FileInfoInterface::exists()
     */
    public function exists()
    {
        return true;
    }

    /**
     * @see \rampage\io\FileInfoInterface::getFilesystem()
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @see \rampage\io\FileInfoInterface::getRelativePath()
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * Won't do anything, but returning this instance
     *
     * @return self
     */
    public function open($mode = null)
    {
        return $this;
    }

    /**
     * Will return a stream resource for this file
     *
     * NOTE: The $mode parameter is ignored. The implementation will use the originally provided open mode!
     *
     * @see \rampage\io\FileInfoInterface::resource()
     * @return resource
     */
    public function resource($mode = null)
    {
        return fopen($this->getPathname(), $this->openMode);
    }
}
