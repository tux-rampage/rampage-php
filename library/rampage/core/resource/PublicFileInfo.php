<?php
/**
 * This is part of rampage.php
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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\resource;

/**
 * Info class for public files
 */
class PublicFileInfo
{
    /**
     * @var string
     */
    private $relativePath = null;

    /**
     * @var string
     */
    private $urlType = null;

    /**
     * @param string $relativePath
     * @param string $urlType
     */
    public function __construct($relativePath, $urlType = null)
    {
        $this->relativePath = $relativePath;
        $this->urlType = $urlType;
    }

    /**
     * Set state for var_export
     *
     * @param array $data
     * @return \rampage\core\resource\PublicFileInfo
     */
    public static function __set_state($data)
    {
        $path = (isset($data['relativePath']))? $data['relativePath'] : false;
        $type = ($data['urlType'])? $data['urlType'] : null;

        return new static($path, $type);
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        $isValid = (is_string($this->relativePath) && ($this->relativePath != ''));
        return $isValid;
    }

    /**
     * @return string
     */
    public function getUrlType()
    {
        return $this->urlType;
    }

    /**
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }
}