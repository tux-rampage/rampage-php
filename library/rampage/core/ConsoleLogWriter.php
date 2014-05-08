<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2013 Axel Helmert
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

namespace rampage\core;

use Zend\Log\Writer\AbstractWriter as AbstractLogWriter;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\Console\ColorInterface as ConsoleColorInterface;

/**
 * Console adapter
 */
class ConsoleLogWriter extends AbstractLogWriter
{
    /**
     * @var ConsoleAdapterInterface
     */
    protected $adapter = null;

    /**
     * @param ConsoleAdapterInterface $adapter
     * @return self
     */
    public function setConsoleAdapter(ConsoleAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }
    /**
     * @see \Zend\Log\Writer\AbstractWriter::doWrite()
     */
    protected function doWrite(array $event)
    {
        if (!$this->adapter) {
            return;
        }

        $adapter = $this->adapter;
        $prefix = ($event['priority'] != Logger::INFO)? strtolower($event['priorityName']) : '';
        $color = ConsoleColorInterface::NORMAL;
        $prefixColor = ConsoleColorInterface::GRAY;

        if ($event['priority'] < Logger::WARN) {
            $color = ConsoleColorInterface::RED;
            $prefixColor = ConsoleColorInterface::LIGHT_RED;
        } else if ($event['priority'] < Logger::INFO) {
            $color = ConsoleColorInterface::YELLOW;
            $prefixColor = ConsoleColorInterface::LIGHT_YELLOW;
        }

        if ($prefix) {
            $adapter->write('[', ConsoleColorInterface::NORMAL);
            $adapter->write($prefix, $prefixColor);
            $adapter->write('] ', ConsoleColorInterface::NORMAL);
        }

        $adapter->write($event['message'] . "\n", $color);
    }
}
