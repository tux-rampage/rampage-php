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

namespace rampage\core\services;

use rampage\core\UserConfig;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserConfigFactory implements FactoryInterface
{
    /**
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $pathManager = $serviceLocator->get('rampage.PathManager');
        $instance = new UserConfig();

        $instance->addFile($pathManager->get('etc', 'userconfig.xml'), -100);

        if (isset($config['config']['userconfigs'])
          && (is_array($config['config']['userconfigs'])
          || ($config['config']['userconfigs'] instanceof \Traversable))) {
            foreach ($config['config']['userconfigs'] as $file => $prio) {
                if (!is_string($file)) {
                    $file = $prio;
                    $prio = 1;
                }

                $instance->addFile($file, $prio);
            }
        }

        return $instance;
    }
}
