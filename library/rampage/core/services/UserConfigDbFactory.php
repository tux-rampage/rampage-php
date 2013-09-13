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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm\services;

use rampage\core\UserConfigInterface;
use rampage\simpleorm\exceptions;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\Db\Adapter\Adapter;

/**
 * Factory for creating a DB-Adapter from userconfig
 */
class UserConfigDbFactory implements FactoryInterface
{
    /**
     * @var string
     */
    protected $configKey = 'db.default';

    /**
     * @var array
     */
    protected $defaults = array();

    /**
     * @param string $configKey
     */
    public function __construct($configKey = null, array $defaults = array())
    {
        if ($configKey) {
            $this->configKey = $configKey;
        }

        $this->defaults = $defaults;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('rampage.UserConfig');
        if (!$config instanceof UserConfigInterface) {
            throw new exceptions\RuntimeException('Could not locate user config.');
        }

        $conf = $config->getConfigValue($this->configKey);
        if (!is_array($conf)) {
            $conf = array();
        }

        $conf = ArrayUtils::merge($this->defaults, $conf);
        return new Adapter($conf);
    }
}
