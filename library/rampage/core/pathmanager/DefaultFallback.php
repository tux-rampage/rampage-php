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

namespace rampage\core\pathmanager;

use Zend\Stdlib\SplPriorityQueue;
use SplFileInfo;

/**
 * Default path fallback implementation
 *
 * @author unreality
 */
class DefaultFallback extends SplPriorityQueue implements FallbackInterface
{
	/**
     * (non-PHPdoc)
     * @see \rampage\core\pathmanager\FallbackInterface::addPath()
     */
    public function addPath($path, $priority = null)
    {
        if ($priority === null) {
            $priority = 0;
        }

        $this->insert($path, $priority);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\pathmanager\FallbackInterface::resolve()
     */
    public function resolve($file)
    {
        $info = null;

        $this->rewind();
        foreach (clone $this as $path) {
            $info = new SplFileInfo(rtrim($path, '/') . '/' . ltrim($file, '/'));

            if ($info->isDir() || $info->isFile() || $info->isLink()) {
                break;
            }
        }

        return ($info)? $info->getPathname() : null;
    }
}
