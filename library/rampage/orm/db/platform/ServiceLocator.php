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

namespace rampage\orm\db\platform;

use rampage\core\service\AbstractObjectLocator;

/**
 * Service locator for DB Platform instances
 */
class ServiceLocator extends AbstractObjectLocator
{
    /**
     * Only allow defined invokables
     *
     * @var bool
     */
    protected $strict = true;

    /**
     * Service locator
     *
     * @param string $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->setServiceClass('default', 'rampage.orm.db.platform.DefaultPlatform');
        $config->configurePlatformServiceLocator($this);
    }

    /**
     * Returns the db platform
     *
     * @return object
     */
    public function get($name, array $options = array())
    {
        if (!$this->has($name)) {
            $name = 'default';
        }

        return parent::get($name, $options);
    }
}