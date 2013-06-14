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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm\metadata\annotation;

use Zend\Code\Scanner\AnnotationScanner;
use FilterIterator;
use Traversable;
use Closure;

/**
 * Annotation iterator
 */
class AnnotationIterator extends FilterIterator
{
    /**
     * @var array
     */
    private $classFilter = array();

    /**
     * @param AnnotationScanner $annotations
     */
    public function __construct(AnnotationScanner $annotations)
    {
        parent::__construct($annotations->getIterator());
    }

    /**
     * @param string|array|Traversable|Closure $filter
     */
    public function setTypeFilter($filter)
    {
        if (!$filter) {
            $this->clearTypeFilter();
            return $this;
        }

        if (!is_array($filter) && !($filter instanceof Traversable) && !($filter instanceof Closure)) {
            $filter = array($filter);
        }

        $this->classFilter = $filter;
        return $this;
    }

    /**
     * Clear type filter
     *
     * @return \rampage\simpleorm\metadata\annotation\AnnotationIterator
     */
    public function clearTypeFilter()
    {
        $this->classFilter = null;
        return $this;
    }

    /**
     * @see FilterIterator::accept()
     */
    public function accept()
    {
        if ($this->classFilter === null) {
            return true;
        }

        if ($this->classFilter instanceof Closure) {
            $filter = $this->classFilter;
            return $filter($this->current());
        }

        foreach ($this->classFilter as $class) {
            if ($this->current() instanceof $class) {
                return true;
            }
        }

        return false;
    }
}