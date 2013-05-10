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

namespace rampage\orm\db\query;

use rampage\core\service\AbstractObjectLocator;
use rampage\core\ObjectManagerInterface;

/**
 * Query mapper service locator
 */
class MapperServiceLocator extends AbstractObjectLocator
{
    /**
     * Default query mapper class
     *
     * @var string
     */
    protected $defaultClass = 'rampage.orm.db.query.DefaultMapper';

    /**
     * @see \rampage\core\service\AbstractObjectLocator::__construct()
     */
    public function __construct(ObjectManagerInterface $objectManager, array $options = array())
    {
        parent::__construct($objectManager);
        $this->requiredInstanceType = 'rampage\orm\db\query\MapperInterface';

        foreach ($options as $name => $class) {
            $this->setServiceClass($name, $class);
        }
    }

    /**
     * Set the default mapper class
     *
     * @param string $class
     */
    public function setDefaultClass($class)
    {
        $this->defaultClass = $class;
        return $this;
    }

    /**
     * @see \rampage\core\service\AbstractObjectLocator::get()
     */
    public function get($name, array $options = array())
    {
        $cName = $this->canonicalizeName($name);
        if (!isset($this->invokables[$cName])) {
            $name = $this->defaultClass;
        }

        return parent::get($name, $options);
    }
}
