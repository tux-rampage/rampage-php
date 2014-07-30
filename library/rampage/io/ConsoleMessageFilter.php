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

namespace rampage\io;

use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\Console\ColorInterface;


class ConsoleMessageFilter
{
    /**
     * @var ConsoleAdapterInterface
     */
    protected $adapter = null;

    /**
     * Color mapping
     *
     * @var array
     */
    protected $colors = array(
        'warning' => ColorInterface::YELLOW,
        'error' => ColorInterface::LIGHT_RED,
        'notice' => ColorInterface::GRAY,
        'debug' => ColorInterface::GRAY,
    );

    /**
     * @param ConsoleAdapterInterface $adapter
     */
    public function __construct(ConsoleAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

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
     * @param string $name
     * @param int $color See Zend\Console\ColorInterface for available colors
     * @return self
     */
    public function setColor($name, $color)
    {
        $this->colors[$name] = $color;
        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    public function clearColor($name = null)
    {
        if ($name === null) {
            $this->colors = array();
        }

        unset($this->colors[$name]);
        return $this;
    }

    /**
     * @param array $match
     */
    protected function colorize($match)
    {
        $text = $this->filter($match['text']);
        $type = $match['type'];

        if (isset($this->colors[$type])) {
            $text = $this->adapter->colorize($text, $this->colors[$type]);
        }

        return $text;
    }

    /**
     * @param string $message
     * @return string
     */
    public function filter($message)
    {
        $callback = array($this, 'colorize');
        $message = preg_replace_callback('~<(?P<type>[a-z]+)>(?P<text>.*)</\1>~s', $callback, $message);
        return $message;
    }
}
