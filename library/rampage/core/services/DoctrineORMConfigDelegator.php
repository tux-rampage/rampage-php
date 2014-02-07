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

use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\Configuration;

/**
 * Delegator for injecting correct paths into the ORM configuration
 */
class DoctrineORMConfigDelegator implements DelegatorFactoryInterface
{
    /**
     * {@inheritdoc}
     * @see \Zend\ServiceManager\DelegatorFactoryInterface::createDelegatorWithName()
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        $config = $callback();

        if ($config instanceof Configuration) {
            $pathManager = $serviceLocator->get('PathManager');
            $dir = $config->getProxyDir();
            $dir = (strpos($dir, 'data/') === 0)? substr($dir, 0, 5) : 'doctrine/proxies';

            $config->setProxyDir($pathManager->get('var', $dir));
        }

        return $config;
    }
}
