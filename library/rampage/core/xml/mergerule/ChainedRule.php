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

namespace rampage\core\xml\mergerule;

use rampage\core\xml\MergeRuleInterface;
use rampage\core\xml\SimpleXmlElement;

use IteratorAggregate;
use ArrayIterator;
use Countable;

/**
 * chain multiple rules.
 *
 * The first rule that matches will be the one used
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2010 Axel Helmert
 */
class ChainedRule implements MergeRuleInterface, IteratorAggregate, Countable
{
	/**
	 * rules
	 *
	 * @var array
	 */
	private $_items = array();

	/**
	 * add a rule
	 *
	 * @param xml\MergeRuleInterface $rule
	 * @param string $name
	 */
	public function add(MergeRuleInterface $rule, $name = null)
	{
	    if (!empty($name)) {
	    	$this->_items[$name] = $rule;
	    } else {
	    	$this->_items[] = $rule;
	    }

	    return $this;
	}

	/**
	 * Clear this chain
	 */
	public function clear()
	{
	    $this->_items = array();
	    return $this;
	}

	/**
	 * Implementation of IteratorAggregte::getIterator()
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
	    return new ArrayIterator($this->_items);
	}

	/**
	 * Implementation of Countable::count()
	 *
	 * @return int
	 */
	public function count()
	{
	    return count($this->_items);
	}

	/**
	 * (non-PHPdoc)
	 * @see rampage\core\xml.MergeRuleInterface::__invoke()
	 */
	public function __invoke(SimpleXmlElement $parent, SimpleXmlElement $newChild, &$affected = null)
	{
		$result = false;

	    foreach ($this->_items as $rule) {
	    	$result = $rule($parent, $newChild, $affected);

	    	if ($result !== false) {
	    		break;
	    	}
	    }

	    return $result;
	}
}