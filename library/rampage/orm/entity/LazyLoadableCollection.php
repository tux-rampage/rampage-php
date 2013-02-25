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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\entity;

use rampage\orm\entity\feature\LazyCollectionInterface;

/**
 * Lazy loadable collection
 */
class LazyLoadableCollection extends Collection implements LazyCollectionInterface
{
    /**
     * Load delegate
     *
     * @var callable
     */
    protected $loadDelegate = null;

    /**
     * Loaded flag
     *
     * @var bool
     */
    protected $isLoaded = false;

    /**
     * (non-PHPdoc)
     * @see \rampage\core\data\Collection::count()
     */
    public function count()
    {
        $this->load();
        return parent::count();
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\data\Collection::getIterator()
     */
    public function getIterator()
    {
        $this->load();
        return parent::getIterator();
    }

    /**
     * Load delegate
     *
     * @param callable $delegate
     * @return \rampage\orm\entity\LazyLoadableCollection
     */
    public function setLoaderDelegate($delegate)
    {
        $this->loadDelegate = $delegate;
        return $this;
    }

    /**
     * Load collection
     *
     * @return \rampage\orm\entity\LazyLoadableCollection
     */
    public function load()
    {
        if ($this->isLoaded || !is_callable($this->loadDelegate)) {
            return $this;
        }

        call_user_func($this->loadDelegate, $this);
        $this->isLoaded = true;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\data\Collection::reset()
     */
    public function reset()
    {
        $this->isLoaded = false;
        return parent::reset();
    }
}