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

namespace rampage\core\xml;

use SplFileInfo;
use Traversable;
use Zend\Stdlib\SplPriorityQueue;

/**
 * Aggregated XML config
 */
abstract class AggregatedXmlConfig extends XmlConfig
{
    /**
     * @var SplPriorityQueue
     */
    private $files = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->files = new SplPriorityQueue();
    }

    /**
     * @param string $file
     * @param int $priority
     */
    public function addFile($file, $priority = 0)
    {
        $this->files->insert($file, $priority);
        return $this;
    }

    /**
     * @param array|Traversable $files
     * @return self
     */
    public function setFiles($files)
    {
        if (!is_array($files) && !($files instanceof Traversable)) {
            return $this;
        }

        foreach ($files as $key => $value) {
            if (is_string($key) && is_int($value)) {
                $this->addFile($key, $value);
            } else {
                $this->addFile($value);
            }
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::_init()
     */
    protected function loadXml()
    {
        $this->setXml('<config></config>');
        $files = clone $this->files;

        foreach ($files as $filePath) {
            $file = new SplFileInfo($filePath);
            if (!$file->isFile() || !$file->isReadable()) {
                continue;
            }

            $ref = new XmlConfig($file->getPathname());
            $this->merge($ref, true);

            unset($ref);
        }

        return $this;
    }
}