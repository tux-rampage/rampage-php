<?php
/**
 * This is part of @application_name@
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
 * @package   @package_name@
 * @author    Axel Helmert
 * @copyright Copyright (c) 2012 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager as ZendServiceManager;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ModuleManager\ModuleEvent;

/**
 * Custom service configuration
 */
class ServiceConfig extends ServiceManagerConfig
{
    /**
     * Path manager config
     *
     * @var array|string|null
     */
    protected $pathManagerConfig = null;

    /**
     * Package config
     *
     * @var array|null
     */
    protected $_packageConfig = null;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->invokables['SharedEventManager'] = 'rampage\core\event\SharedEventManager';
        $this->factories['rampage.ModuleRegistry'] = 'rampage\core\service\ModuleRegistryFactory';
        $this->pathManagerConfig = isset($config['path_manager'])? $config['path_manager'] : null;

        parent::__construct($config);
    }

    /**
     * Pathmanager config
     *
     * @return string|array|null
     */
    protected function getPathManagerConfig()
    {
        return $this->pathManagerConfig;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\Configuration::configureServiceManager()
     */
    public function configureServiceManager(ZendServiceManager $serviceManager)
    {
        $pathManager = new PathManager($this->getPathManagerConfig());
        $serviceManager->setService('rampage.PathManager', $pathManager, true);

        parent::configureServiceManager($serviceManager);

        $serviceManager->addInitializer(function($instance, $serviceManager) {
            if ($instance instanceof ModuleManagerInterface) {
                $instance->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES, $serviceManager->get('rampage.ModuleRegistry'), 9100);
            }
        });

        return $this;
    }
}