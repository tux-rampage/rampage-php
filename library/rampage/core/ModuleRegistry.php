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

use Zend\ModuleManager\ModuleEvent;
use rampage\core\exception\RuntimeException;

/**
 * Listener for modules initialization
 */
class ModuleRegistry implements \IteratorAggregate
{
    const STATIC_FILE = 'modules.compiled.php';

    /**
     * Path manager instance
     *
     * @var PathManager
     */
    private $pathManager = null;

    /**
     * Available modules
     *
     * @var array
     */
    protected $modules = null;

    /**
     * Construct
     *
     * @param PathManager $pathManager
     */
    public function __construct(PathManager $pathManager = null)
    {
        if ($pathManager) {
            $this->setPathManager($pathManager);
        }
    }

    /**
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        $this->initModules();
        return new \ArrayIterator($this->modules);
    }

    /**
     * Initialize modules
     *
     * @return \rampage\core\ModuleManager
     */
    public function initModules()
    {
        if ($this->modules !== null) {
            return $this;
        }

        // try precompiled
        $file = $this->getPathManager()->get('etc', self::STATIC_FILE);
        $modules = (is_readable($file))? include $file : null;

        if (is_array($modules)) {
            $registry = $this;
            $this->modules = array_filter($modules, function($item) use ($registry) {
                if (!$item instanceof Module) {
                    return false;
                }

                $item->setRegistry($registry);
                return true;
            });

            return $this;
        }

        // fetch from config
        $file = $this->getPathManager()->get('etc', 'modules.conf');
        if (!file_exists($file)) {
            $this->modules = array();
            return $this;
        }

        $data = parse_ini_file($file, false);
        if (!is_array($data)) {
            throw new exception\RuntimeException('Invalid configuration data in ' . $file);
        }

        $this->setModuleConfig($data);
        return $this;
    }

    /**
     * Set the module config
     *
     * @param array $moduleConfig
     * @return \rampage\core\ModuleRegistry
     */
    public function setModuleConfig(array $moduleConfig)
    {
        if ($this->modules !== null) {
            throw new RuntimeException('Cannot set module configuration on already initialized module registry.');
        }

        foreach ($moduleConfig as $name => $option) {
            $conf = array();
            if (is_string($option) && !empty($option) && ($option != '1')) {
                $conf['path'] = $option;
            } else if (!$option) {
                continue;
            }

            $module = new Module($name, $conf);
            $module->setRegistry($this);
            $this->modules[$name] = $module;
        }

        return $this;
    }

    /**
     * Set the path manager
     *
     * @param PathManager $manager
     * @return \rampage\core\DiscoverListener
     */
    public function setPathManager(PathManager $manager)
    {
        $this->pathManager = $manager;
        return $this;
    }

    /**
     * Returns the path manager
     *
     * @return \rampage\core\PathManager
     */
    public function getPathManager()
    {
        if (!$this->pathManager) {
            throw new exception\RuntimeException('Missing dependency: path manager');
        }

        return $this->pathManager;
    }

    /**
     * Return module names
     *
     * @return array
     */
    public function getModuleNames()
    {
        $this->initModules();
        return array_keys($this->modules);
    }

    /**
     * Return a specific module
     *
     * @param string $name
     * @return \rampage\core\Module
     */
    public function getModule($name)
    {
        if (!isset($this->modules[$name])) {
            return false;
        }

        return $this->modules[$name];
    }

    /**
     * Invoke bootstrap listener
     *
     * @param ModuleEvent $event
     */
    public function __invoke(ModuleEvent $event)
    {
        if ($event->getName() == ModuleEvent::EVENT_LOAD_MODULES) {
            $this->initModules();

            /* @var $manager \Zend\ModuleManager\ModuleManager */
            $moduleManager = $event->getTarget();
            $modules = $this->getModuleNames();
            $events = $moduleManager->getEventManager();

            $events->attach(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, $this, 9100);
            $moduleManager->setModules($modules);

            return $this;
        }

        $name = $event->getModuleName();
        $module = $this->getModule($name);

        if ($module && !$module->load()->isValid()) {
            return false;
        }

        if ($module && $module->hasInstance()) {
            return $module->getInstance();
        }

        return $module;
    }

    /**
     * Save static definition for faster module definition loading
     *
     * @param string $file
     * @throws exception\RuntimeException
     * @return \rampage\core\ModuleManager
     */
    public function saveStaticDefinition($file = null)
    {
        if (!$file) {
            $file = $this->getPathManager()->get('etc', self::STATIC_FILE);
        }

        $data = array();
        foreach ($this->modules as $name => $module) {
            $data[$name] = new modules\ModuleEntry($name, $module->getOptions());
        }

        $export = '<?php return ' . var_export($data, true) . ';';
        if (file_put_contents($file, $export) === false) {
            throw  new exception\RuntimeException('Failed to write module config to '.$file);
        }

        return $this;
    }
}
