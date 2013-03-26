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

namespace rampage\orm\entity\lazy;

use rampage\orm\entity\Collection as EntityCollection;
use rampage\orm\entity\lazy\delegate\CollectionLoaderInterface;

/**
 * Lazy loadable collection
 */
class Collection extends EntityCollection implements CollectionInterface
{
    /**
     * Load delegate
     *
     * @var \rampage\orm\entity\lazy\delegate\CollectionLoaderInterface
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
     * Load collection
     *
     * @return \rampage\orm\entity\Collection
     */
    public function load()
    {
        if ($this->isLoaded || !($this->loadDelegate instanceof CollectionLoaderInterface)) {
            return $this;
        }

        $this->loadDelegate->load($this);
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

    /**
     * (non-PHPdoc)
     * @see \rampage\core\data\Collection::getSize()
     */
    public function getSize()
    {
        if (($this->size === null) && ($this->loadDelegate instanceof CollectionLoaderInterface)) {
            $this->loadDelegate->loadSize($this);
        }

        return parent::getSize();
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\entity\lazy\CollectionInterface::setLoaderDelegate()
     */
    public function setLoaderDelegate(CollectionLoaderInterface $delegate)
    {
        $this->reset();
        $this->loadDelegate = $delegate;

        return $this;
    }
}