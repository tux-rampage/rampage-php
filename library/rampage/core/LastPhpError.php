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

namespace rampage\core;

/**
 * Provides a consistent interface to the last PHP error
 */
class LastPhpError
{
    /**
     * @var GracefulArrayAccess
     */
    protected $info = null;

    /**
     * @var bool
     */
    protected $isError = false;

    /**
     * Initialize instance with the last PHP error info
     */
    public function __construct()
    {
        $info = error_get_last();

        $this->isError = ($info !== null);
        $this->info = new GracefulArrayAccess($info?: array());
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return $this->isError;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->info->get('message');
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->info->get('type');
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->info->get('file');
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->info->get('line');
    }
}
