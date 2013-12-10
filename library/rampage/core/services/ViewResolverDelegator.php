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

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\View\Resolver\AggregateResolver;

class ViewResolverDelegator implements DelegatorFactoryInterface
{
    /**
     * @see \Zend\ServiceManager\DelegatorFactoryInterface::createDelegatorWithName()
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        /* @var $resolver \Zend\View\Resolver\AggregateResolver */
        $resolver = call_user_func($callback);
        if ($resolver instanceof AggregateResolver) {
            $resolver->attach($serviceLocator->get('rampage\core\view\TemplateLocator'), 100);
        }

        return $resolver;
    }
}
