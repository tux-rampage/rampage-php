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

namespace rampage\ui\services;

use rampage\ui\ToastContainer;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;

/**
 * Abstract toast plugin factory
 */
abstract class AbstractToastPluginFactory  implements FactoryInterface
{
    /**
     * @var string|ToastContainer
     */
    protected $container = 'rampage\ui\ToastContainer';

    /**
     * @param string $container
     */
    public function __construct($container = null)
    {
        if ($container) {
            $this->container = $container;
        }
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @throws ServiceNotFoundException
     * @return \rampage\ui\ToastContainer
     */
    protected function getContainer(ServiceLocatorInterface $serviceLocator)
    {
        if (is_string($this->container)) {
            $this->container = $serviceLocator->get($this->container);
        }

        if (!$this->container instanceof ToastContainer) {
            throw new ServiceNotFoundException('Failed to create/find toast container service');
        }

        return $this->container;
    }
}
