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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\hydrator\strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * DateTime hydrator strategy
 */
class DateTimeStrategy implements StrategyInterface
{
    /**
     * Date format
     *
     * @var string
     */
    protected $format = 'Y-m-d H:i:s';

    /**
     * Date format
     *
     * @param string $format
     */
    public function __construct($format = null)
    {
        if ($format) {
            $this->format = $format;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Stdlib\Hydrator\Strategy\StrategyInterface::extract()
     */
    public function extract($value)
    {
        if ($value instanceof \DateTime) {
            return $value->toString($this->format);
        }

        return $value;
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\Stdlib\Hydrator\Strategy\StrategyInterface::hydrate()
     */
    public function hydrate($value)
    {
        $date = null;

        if (($value !== null) && !($value instanceof \DateTime)) {
            $value = new \DateTime($value);
        }

        return $date;
    }
}