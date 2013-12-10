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
 * @package   rampage.test
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\test;

use rampage\core\ServiceManager;
use rampage\core\Application;
use rampage\core\ServiceConfig;

/**
 * Abstract integration test case
 */
class IntegrationTestCase extends AbstractTestCase
{
    /**
     * Service manager
     *
     * @var \rampage\core\ServiceManager
     */
    private $serviceManager = null;

    /**
     * Initialize the application's service manager
     *
     * NOTE: This loads all Modules but does not invoke the application boostrap.
     *
     * @param array|string $paths
     * @param ModuleRegistry|array|null $modules
     * @return \rampage\test\IntegrationTestCase
     */
    protected function initFramework($paths, $modules = null, array $config = array())
    {
        $config = Application::mergeConfig($config);

        $serviceConfig = isset($config['service_manager']) ? $config['service_manager'] : array();
        $serviceConfig['path_manager'] = $paths;
        $serviceManager = new ServiceManager(new ServiceConfig($serviceConfig));

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();

        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * Bootstrap the application and return it
     *
     * @return \rampage\core\Application
     */
    protected function bootstrapApplication()
    {
        return $this->getServiceManager()->get('application')->bootstrap();
    }

    /**
     * Set the service manager
     *
     * @param \rampage\core\ServiceManager $serviceManager
     */
    protected function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

	/**
     * Returns the service manager
     *
     * @return \rampage\core\ServiceManager
     */
    protected function getServiceManager()
    {
        if (!$this->serviceManager) {
            throw new \RuntimeException('The service manager is not initialized. Consider calling initFramework() before');
        }

        return $this->serviceManager;
    }
}