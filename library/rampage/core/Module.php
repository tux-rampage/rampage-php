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

use SplFileInfo;
use rampage\core\modules\ManifestConfig;
use rampage\core\modules\ModuleInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface;
use rampage\core\resource\FileLocator;
use rampage\core\exception\RuntimeException;

/**
 * Module info
 */
class Module implements ModuleInterface,
    ConfigProviderInterface,
    AutoloaderProviderInterface,
    ConsoleBannerProviderInterface,
    ConsoleUsageProviderInterface
{
    const STATIC_FILE = 'manifest.compiled.php';

    /**
     * Module registry
     *
     * @var \rampage\core\ModuleRegistry
     */
    private $registry = null;

    /**
     * Current path manager
     *
     * @var PathManager
     */
    private $pathManager = null;

    /**
     * Module name
     *
     * @var string
     */
    private $name = null;

    /**
     * Is loaded flag
     *
     * @var bool
     */
    private $isLoaded = false;

    /**
     * Options
     *
     * @var array
     */
    private $options = array();

    /**
     * Module path
     *
     * @var string
     */
    private $path = null;

    /**
     * Resource locator
     *
     * @var \rampage\core\resource\FileLocator
     */
    private $resourceLocator = null;

    /**
     * Module class name
     *
     * @var string
     */
    private $moduleClass = null;

    /**
     * Manifest data
     *
     * @var array
     */
    private $manifest = null;

    /**
     * Custom module instance
     *
     * @return object
     */
    private $instance = null;

    /**
     * Construct
     *
     * @param string $name
     */
    public function __construct($name, array $options = array())
    {
        $this->name = $name;
        $this->options = $options;
    }

    /**
     * Returns options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

	/**
     * Set the module registry
     *
     * @param ModuleManagerInterface $registry
     */
    public function setRegistry(ModuleRegistry $registry)
    {
        $this->registry = $registry;
        return $this;
    }

    /**
     * Returns the module registriy
     *
     * @return \rampage\core\ModuleRegistry
     */
    protected function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Set the path manager
     *
     * @param PathManager $path
     */
    public function setPathManager(PathManager $path)
    {
        $this->pathManager = $path;
        return $this;
    }

    /**
     * Path manager instance
     *
     * @return PathManager
     */
    protected function getPathManager()
    {
        if (!$this->pathManager) {
            $this->setPathManager($this->getRegistry()->getPathManager());
        }

        return $this->pathManager;
    }

    /**
     * Set loaded flag
     *
     * @param bool $flag
     * @return \rampage\core\Module
     */
    protected function setIsLoaded($flag)
    {
        $this->isLoaded = (bool)$flag;
        return $this;
    }

    /**
     * Resolve a file from module path
     *
     * @param string $file
     * @param bool $asFileInfo
     * @return \SplFileInfo|string
     */
    public function getModulePath($file = null, $asFileInfo = false)
    {
        if (isset($this->options['path'])) {
            $path = $this->options['path'];
        } else {
            if (isset($this->options['_path'])) {
                $info = new SplFileInfo($this->options['_path']);
            } else {
                $info = new SplFileInfo($this->getPathManager()->get('modules', $this->name));
            }

            if ($info->isFile()) {
                $info = new SplFileInfo('phar://' . $info->getPathname());
            }

            $path = $info->getPathname();
            $this->options['path'] = $path;
        }

        if ($file) {
            $path .= '/' . ltrim($file, '/');
        }

        if ($asFileInfo) {
            $path = new SplFileInfo($path);
        }

        return $path;
    }

    /**
     * Returns an resource path
     *
     * @param string $type
     * @param string $path
     * @param string $asFileInfo
     */
    public function getResourcePath($type, $path, $asFileInfo = false)
    {
        if (!$this->resourceLocator) {
            $this->load();
            $name = $this->getName();

            if (!isset($this->manifest['application_config']['rampage']['resources'][$name])) {
                throw new RuntimeException(sprintf('Resource location for module "%s" is not defined', $name));
            }

            $this->resourceLocator = new FileLocator($this->getPathManager());
            $this->resourceLocator->addLocation('__module__', $this->manifest['application_config']['rampage']['resources'][$name]);
        }

        $result = $this->resourceLocator->resolve($type, $path, '__module__', $asFileInfo);
        if ($result === false) {
            throw new RuntimeException('Undefined resource type: ' . $type);
        }

        return $result;
    }

    /**
     * Is loaded check
     */
    public function isLoaded()
    {
        return $this->isLoaded;
    }

    /**
     * Load module
     *
     * @return bool
     */
    public function load()
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->isLoaded = true;
        $manifest = $this->getModulePath(static::STATIC_FILE, true);
        if ($manifest && $manifest->isReadable() && $manifest->isFile()) {
            $data = include $manifest;

            $this->manifest = $data;
            return $this;
        }

        $xmlfile = $this->getModulePath('module.xml', true);

        if ($xmlfile && $xmlfile->isFile() && $xmlfile->isReadable()) {
            $config = new ManifestConfig($this, $this->getModulePath('module.xml'));
            $this->manifest = $config->toArray();
        } else {
            // ZF2 module
            $this->manifest = array('module_instance' => $this->name . '\\Module');
        }

        return $this;
    }

    /**
     * Returns the module name as defined in the module manifest
     *
     * @return string
     */
    public function getName()
    {
        if (isset($this->manifest['name'])) {
            return (string)$this->manifest['name'];
        }

        return $this->name;
    }

    /**
     * Returns the module version
     *
     * @return string|false
     */
    public function getVersion()
    {
        if (isset($this->manifest['version'])) {
            return (string)$this->manifest['version'];
        }

        return false;
    }

    /**
     * Compile manifest
     */
    public function compileManifest($file = null)
    {
        $array = var_export($this->manifest, true);
        $array = str_replace($this->getModulePath(), "' . __DIR__ . '", $array);
        $array = str_replace("'' . __DIR__", "__DIR__", $array);
        $code = "<?php return $array;";

        if (!$file) {
            $file = $this->getModulePath(static::STATIC_FILE);
        }

        if (file_put_contents($file, $code) === false) {
            throw new exception\RuntimeException('Failed to write compiled manifest to ' . $file);
        }

        return $this;
    }

    /**
     * Check validity
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->isLoaded() && is_array($this->manifest);
    }

    /**
     * Check for custom module instance
     *
     * @return bool
     */
    public function hasInstance()
    {
        return (isset($this->manifest['module_instance']) && $this->manifest['module_instance']);
    }

    /**
     * Resolve the module class file
     *
     * @param string $class
     * @throws \rampage\core\exception\RuntimeException
     * @return string
     */
    protected function findModuleClassFile($class)
    {
        if (isset($this->manifest['module_file'])) {
            return $this->getModulePath($this->manifest['module_file']);
        }

        $file = str_replace('\\', '/', $class) . '.php';
        $path = $this->getModulePath($file, true);
        if (!$path->isFile() || !$path->isReadable()) {
            $file = basename($file);
            $path = $this->getModulePath($file, true);

            if (!$path->isFile() || !$path->isReadable()) {
                throw new exception\RuntimeException('Failed to load module class: '.$class);
            }
        }

        $this->manifest['module_file'] = $file;
        return $path->getPathname();
    }

    /**
     * Load module class
     *
     * @param string $class
     * @throws \rampage\core\exception\RuntimeException
     * @return \rampage\core\Module
     */
    protected function loadModuleClass($class)
    {
        if (class_exists($class)) {
            return $this;
        }

        $path = $this->findModuleClassFile($class);
        include_once $path;

        if (!class_exists($class)) {
            throw new exception\RuntimeException('Failed to load module class: '.$class);
        }

        return $this;
    }

    /**
     * Get custom module instance
     *
     * @return object|null
     */
    public function getInstance()
    {
        if ($this->instance) {
            return $this->instance;
        }

        if (!$this->hasInstance()) {
            return null;
        }

        $class = str_replace('.', '\\', $this->manifest['module_instance']);
        $class = trim($class, '\\');

        $this->loadModuleClass($class);
        $this->instance = new $class($this);

        return $this->instance;
    }

    /**
     * Returns the application config
     *
     * @return array
     */
    public function getConfig()
    {
        $config = $this->manifest['application_config'];
//         $this->prepareManifest($config);

        $diConfig = new \SplFileInfo($this->getModulePath('di.config.php'));
        if ($diConfig->isFile() && $diConfig->isReadable()) {
            $config['di'] = include $diConfig->getPathname();
        }

        $definition = new \SplFileInfo($this->getModulePath('di.compiled.php'));
        if ($definition->isFile() && $definition->isReadable()) {
            $config['di']['compiled'][] = $definition->getPathname();
        }


        return $config;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ModuleManager\Feature\ConsoleBannerProviderInterface::getConsoleBanner()
     */
    public function getConsoleBanner(AdapterInterface $console)
    {
        if (isset($this->manifest['console']['banner'])) {
            return $this->manifest['console']['banner'];
        }

        return false;
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\ModuleManager\Feature\ConsoleUsageProviderInterface::getConsoleUsage()
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        if (isset($this->manifest['console']['usage'])) {
            return $this->manifest['console']['usage'];
        }

        return array();
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\ModuleManager\Feature\AutoloaderProviderInterface::getAutoloaderConfig()
     */
    public function getAutoloaderConfig()
    {
        $file = $this->getModulePath('autoload.config.php', true);
        if ($file->isFile() && $file->isReadable()) {
            return include $file->getPathname();
        }

        return $this->manifest['autoloader_config'];
    }
}