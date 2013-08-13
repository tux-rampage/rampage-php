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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm\hydration;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use DateTime;

/**
 * Datetime hydration strategy
 */
class DateTimeStrategy implements StrategyInterface
{
    const DEFAULT_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var format
     */
    protected $format = null;

    /**
     * @param string $format
     */
    public function __construct($format = null)
    {
        $this->format = $format;
    }

    /**
     * @see \Zend\Stdlib\Hydrator\Strategy\StrategyInterface::extract()
     */
    public function extract($value)
    {
        if (($value === null) || ($value instanceof DateTime)) {
            return $value;
        }

        $format = $this->format? : self::DEFAULT_FORMAT;
        return DateTime::createFromFormat($this->format, $value);
    }

    /**
     * @see \Zend\Stdlib\Hydrator\Strategy\StrategyInterface::hydrate()
     */
    public function hydrate($value)
    {
        if (!$value instanceof DateTime) {
            return $value;
        }

        $format = $this->format? : self::DEFAULT_FORMAT;
        return $value->format($format);
    }
}
