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

namespace rampage\orm\db\hydrator;

use rampage\core\service\AbstractObjectLocator;
use rampage\core\ObjectManagerInterface;

/**
 * Strategy manager
 *
 * @method \Zend\Stdlib\Hydrator\Strategy\StrategyInterface get() get($name, $options)
 */
class StrategyManager extends AbstractObjectLocator
{
	/**
     * @see \rampage\core\service\AbstractObjectLocator::__construct()
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->strict = true;
        $this->requiredInstanceType = 'Zend\Stdlib\Hydrator\Strategy\StrategyInterface';
        $this->invokables = array(
            'cursor' => 'rampage.orm.db.hydrator.CursorStrategy',
            'collection' => 'rampage.orm.db.hydrator.CollectionStrategy',
            'collectiondelegate' => 'rampage.orm.db.hydrator.CollectionDelegateStrategy',
            'entity' => 'rampage.orm.db.hydrator.ReferenceStrategy',
            'lazyentity' => 'rampage.orm.db.hydrator.LazyReferenceStrategy',
        );

        parent::__construct($objectManager);
    }
}