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
use Exception;
use Zend\Console\ColorInterface;

class ConsoleIO extends AbstractIO
{
    /**
     * @var ConsoleAdapterInterface
     */
    protected $adapter = null;

    protected $filter = null;

    /**
     * @param ConsoleAdapterInterface $adapter
     */
    public function __construct(ConsoleAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        $this->filter = new ConsoleMessageFilter($adapter);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\io\IOInterface::write()
     */
    public function write($message, $level = null)
    {
        if ($this->isSilent()) {
            return;
        }

        if ($level && ($this->verbosity < $level)) {
            return;
        }

        $message = $this->filter->filter($message);
        $this->adapter->write($message);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\io\IOInterface::writeLine()
     */
    public function writeLine($line, $level = null)
    {
        $this->write($line . PHP_EOL, $level);
    }

    /**
     * @param array $trace
     * @return string
     */
    protected function renderTrace(array $trace)
    {
        $lines = array();

        foreach ($trace as $key => $item) {
            if (!isset($item['function']) || ($item['function'] == '')) {
                $call = $this->adapter->colorize('[internal function]', ColorInterface::YELLOW);
            } else {
                $call = (isset($item['class'])? $this->adapter->colorize($item['class'], ColorInterface::BLUE) : '')
                      . (isset($item['type'])? $item['type'] : '')
                      . $this->adapter->colorize($item['function'], ColorInterface::YELLOW) . '()';
            }

            $filePos = (isset($item['file']))? $item['file'] : '-';
            if (isset($item['line'])) {
                $filePos .= ':' . $item['line'];
            }

            $lines[] = sprintf(
                '%s: %s [%s]',
                $this->adapter->colorize('#' . $key, ColorInterface::GRAY),
                $call,
                $this->adapter->colorize($filePos, ColorInterface::GRAY)
            );
        }

        return implode("\n", $lines);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\io\IOInterface::writeException()
     */
    public function writeException(Exception $e)
    {
        $class = get_class($e);
        $text = $this->adapter->colorize("Exception - $class:\n{$e->getMessage()}\n", ColorInterface::LIGHT_RED);

        if ($this->isVerbose()) {
            $text .= "\n" . $this->renderTrace($e->getTrace()) . "\n";

            if ($this->isVeryVerbose() && ($previous = $e->getPrevious())) {
                $text .= "\n" . $this->adapter->colorize('Previous Exceptions:', ColorInterface::YELLOW) . "\n\n";

                do {
                    $class = $this->adapter->colorize(get_class($previous), ColorInterface::YELLOW);
                    $msg = $this->adapter->colorize($previous->getMessage(), ColorInterface::GRAY);
                    $text .= sprintf('%s: %s', $class, $msg)
                           . "\n" . $this->renderTrace($e->getTrace()) . "\n\n";
                } while ($previous = $previous->getPrevious());
            }
        }

        $this->adapter->writeLine($text);
    }
}
