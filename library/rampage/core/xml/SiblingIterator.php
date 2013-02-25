<?php
/**
 * This is part of @application_name@
 * Copyright (c) 2010 Axel Helmert
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
 * @copyright Copyright (c) 2010 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\xml;

use Iterator;

/**
 * Xml Child iterator
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2010 Axel Helmert
 */
class SiblingIterator implements Iterator
{
    /**
     * Siblings
     *
     * @var array
     */
	private $_items = array();

	/**
	 * Sibling iterator
	 *
	 * @param SimpleXmlElement $element
	 */
	public function __construct(SimpleXmlElement $element)
	{
		foreach ($element as $sibling) {
	    	$this->_items[] = $sibling;
		}

		reset($this->_items);
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::rewind()
	 */
	public function rewind()
	{
	    reset($this->_items);
	}

	/**
	 * Returns the current item
	 *
	 * @return \rampage\core\xml\SiblingIterator
	 */
	public function current()
	{
	    return current($this->_items);
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::valid()
	 */
	public function valid()
	{
	    return ($this->current() !== false);
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::next()
	 */
	public function next()
	{
	    next($this->_items);
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::key()
	 */
	public function key()
	{
		if (!$this->valid()) {
			return null;
		}

	    return $this->current()->getName();
	}
}