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

namespace rampage\orm\query\constraint;

use rampage\core\service\AbstractObjectLocator;
use rampage\core\ObjectManagerInterface;

/**
 * Constraint locator
 */
class ConstraintLocator extends AbstractObjectLocator
{
    /**
     * (non-PHPdoc)
     * @see \rampage\core\service\AbstractObjectLocator::__construct()
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager);

        $this->strict = true;
        $this->invokables = array(
            Composite::TYPE_AND => 'rampage.orm.query.constraint.Composite',
            Composite::TYPE_OR => 'rampage.orm.query.constraint.Composite',
            DefaultConstraint::TYPE_COMPARE => 'rampage.orm.query.constraint.DefaultConstraint',
            DefaultConstraint::TYPE_EQUALS => 'rampage.orm.query.constraint.DefaultConstraint',
            DefaultConstraint::TYPE_ISNULL => 'rampage.orm.query.constraint.DefaultConstraint',
            DefaultConstraint::TYPE_LIKE => 'rampage.orm.query.constraint.DefaultConstraint',
            DefaultConstraint::TYPE_NOTEQUALS => 'rampage.orm.query.constraint.DefaultConstraint',
            DefaultConstraint::TYPE_NOTNULL => 'rampage.orm.query.constraint.DefaultConstraint',
            DefaultConstraint::TYPE_NOTLIKE => 'rampage.orm.query.constraint.DefaultConstraint',
            DefaultConstraint::TYPE_IN => 'rampage.orm.query.constraint.DefaultConstraint',
        );
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\service\AbstractObjectLocator::get()
     * @return \rampage\orm\query\constraint\ConstraintInterface
     */
    public function get($name, array $options = array())
    {
        $options['type'] = $name;
        return parent::get($name, $options);
    }
}