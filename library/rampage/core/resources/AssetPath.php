<?php
/**
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

namespace rampage\core\resources;


class AssetPath
{
    /**
     * @var string
     */
    protected $path = null;

    /**
     * @var string
     */
    protected $scope = null;

    /**
     * @param string $path
     * @param string $scope
     */
    public function __construct($path, $scope = null)
    {
        $this->scope = (string)$scope;
        $this->path = $path;
        $findScope = (($this->scope == '') && ($scope !== false));

        if ($findScope && (substr($path, 0, 1) == '@')) {
            if (strpos($path, '/') !== false) {
                list($this->scope, $this->path) = explode('/', substr($path, 1), 2);
            } else {
                $this->path = substr($path, 1);
            }
        } else if ($findScope && (strpos($path, '::') !== false)) {
            list($this->scope, $this->path) = explode('::', $path, 2);
        }

        if ($this->scope == '') {
            $this->scope = false;
        }
    }
    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string|bool The scope name of false if no scope should be used
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->scope == '') {
            return $this->path;
        }

        return '@' . $this->scope  . '/' . $this->path;
    }
}
