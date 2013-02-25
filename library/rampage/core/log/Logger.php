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

namespace rampage\core\log;

// ZF2 imports
use Zend\Log\Logger as DefaultLogger;
use Zend\Log\Exception;
use Zend\Stdlib\ArrayUtils;

// SPL imports
use DateTime;
use Traversable;


/**
 * Custom logger implementation
 */
class Logger extends DefaultLogger
{
    /**
     * Add a message as a log entry
     *
     * In contrast of the zend implementation this does NOT cast the message to a
     * string, nor does it require a stringifyable object.
     *
     * Leaving handling the message to the log writer makes much more sense then
     * the current, shitty implementation.
     *
     * @param  int $priority
     * @param  mixed $message
     * @param  array|Traversable $extra
     * @return Logger
     * @throws Exception\InvalidArgumentException if message can't be cast to string
     * @throws Exception\InvalidArgumentException if extra can't be iterated over
     * @throws Exception\RuntimeException if no log writer specified
     */
    public function log($priority, $message, $extra = array())
    {
        if (!is_int($priority) || ($priority<0) || ($priority>=count($this->priorities))) {
            throw new Exception\InvalidArgumentException(sprintf(
                '$priority must be an integer > 0 and < %d; received %s',
                count($this->priorities),
                var_export($priority, 1)
            ));
        }

        if (!is_array($extra) && !$extra instanceof Traversable) {
            throw new Exception\InvalidArgumentException(
                '$extra must be an array or implement Traversable'
            );
        } else if ($extra instanceof Traversable) {
            $extra = ArrayUtils::iteratorToArray($extra);
        }

        if ($this->writers->count() === 0) {
            throw new Exception\RuntimeException('No log writer specified');
        }

        $timestamp = new DateTime();

        if (is_array($message)) {
            $message = var_export($message, true);
        }

        foreach ($this->writers->toArray() as $writer) {
            $writer->write(array(
                'timestamp'    => $timestamp,
                'priority'     => (int) $priority,
                'priorityName' => $this->priorities[$priority],
                'message'      => $message,
                'extra'        => $extra
            ));
        }

        return $this;
    }
}