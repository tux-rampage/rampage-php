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

namespace rampage\filesystem;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileObject;
use SplFileInfo;


/**
 * Writable local filesystem
 */
class WritableLocalFilesystem extends LocalFilesystem implements WritableFilesystemInterface
{
    /**
     * @var int
     */
    protected $dirMode = 0775;

    /**
     * {@inheritdoc}
     * @see \rampage\filesystem\LocalFilesystem::__construct()
     */
    public function __construct($baseDir, $dirMode = null)
    {
        if ($dirMode !== null) {
            $this->dirMode = $dirMode;
        }

        parent::__construct($baseDir);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\filesystem\WritableFilesystemInterface::delete()
     */
    public function delete($path, $recursive = false)
    {
        if ($path instanceof FileInfoInterface) {
            $path = $path->getRelativePath();
        }

        $info = $this->info($path);
        if (!$info->exists()) {
            return $this;
        }

        if (!$info->isDir()) {
            if (!unlink($info->getStreamUrl())) {
                throw new RuntimeException(sprintf(
                    'Failed to delete file "%s": %s',
                    $path, $this->getLastPhpError()
                ));
            }

            return $this;
        }

        if ($recursive) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($info->getStreamUrl()), RecursiveIteratorIterator::CHILD_FIRST);

            /* @var $child FileInfoInterface */
            foreach ($iterator as $child) {
                if ($child->isDir()) {
                    $result = @rmdir($child->getStreamUrl());
                } else {
                    $result = @unlink($child->getStreamUrl());
                }

                if (!$result) {
                    throw new RuntimeException(sprintf(
                        'Failed to remove entry "%s": %s',
                        $child->getRelativePath(), $this->getLastPhpError()
                    ));
                }
            }
        }

        if (!rmdir($info->getStreamUrl())) {
            throw new RuntimeException(sprintf(
                'Failed to remove directory "%s": %s',
                $path, $this->getLastPhpError()
            ));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\filesystem\WritableFilesystemInterface::mkdir()
     */
    public function mkdir($path)
    {
        $info = $this->info($path);
        if ($info->isDir()) {
            return $this;
        }

        if (!mkdir($info->getStreamUrl(), $this->dirMode, true)) {
            throw new RuntimeException(sprintf(
                'Failed to create directory "%s": %s',
                $path, $this->getLastPhpError()
            ));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\filesystem\WritableFilesystemInterface::touch()
     */
    public function touch($path, $time = null, $atime = null)
    {
        $info = $this->info($path);
        return @touch($info->getStreamUrl(), $time, $atime);
    }

    /**
     * @see \rampage\filesystem\LocalFilesystem::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $target = $this->info($offset);

        if (($value instanceof SplFileInfo) && !($value instanceof SplFileObject)) {
            $value = fopen($value->getPathname(), 'r');
        }

        if (is_resource($value)) {
            $stream = $target->resource('w');
            stream_copy_to_stream($value, $stream);

            fflush($stream);
            fclose($stream);

            return $this;
        }

        $file = $target->open('w');
        $file->ftruncate(0);

        if ($value instanceof SplFileObject) {
            $value->fseek(0, SEEK_SET);

            while (!$value->eof()) {
                $file->fwrite($value->fgets());
            }
        } else {
            $file->fwrite($value);
        }

        $file->fflush();
        $file = null;

        return $this;
    }

    /**
     * @see \rampage\filesystem\LocalFilesystem::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
        return $this;
    }
}
