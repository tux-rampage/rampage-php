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

use rampage\core\resources\StaticResourcePublishingStrategy;

use rampage\io\ConsoleIO;
use rampage\io\NullIO;

use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


/**
 * Factory for resource publishing strategy
 */
class ResourcePublishingStrategyFactory implements FactoryInterface
{
    /**
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $pathManager = $serviceLocator->get('rampage.PathManager');
        $baseUrl = $serviceLocator->get('baseurl.static');
        $console = $serviceLocator->get('console');
        $io = ($console instanceof ConsoleAdapterInterface)? new ConsoleIO($console) : new NullIO();
        $strategy = new StaticResourcePublishingStrategy($pathManager->get('static'), $config, $io);

        $strategy->setBaseUrl($baseUrl);

        return $strategy;
    }
}
