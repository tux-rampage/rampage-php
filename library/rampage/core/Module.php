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
    private $_registry = null;

    /**
     * Current path manager
     *
     * @var PathManager
     */
    private $_pathManager = null;

    /**
     * Module name
     *
     * @var string
     */
    private $_name = null;

    /**
     * Is loaded flag
     *
     * @var bool
     */
    private $_isLoaded = false;

    /**
     * Options
     *
     * @var array
     */
    private $_options = array();

    /**
     * Module path
     *
     * @var string
     */
    private $_path = null;

    /**
     * Module class name
     *
     * @var string
     */
    private $_moduleClass = null;

    /**
     * Manifest data
     *
     * @var array
     */
    private $_manifest = null;

    /**
     * Custom module instance
     *
     * @return object
     */
    private $_instance = null;

    /**
     * Construct
     *
     * @param string $name
     */
    public function __construct($name, array $options = array())
    {
        $this->_name = $name;
        $this->_options = $options;
    }

    /**
     * Returns options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

	/**
     * Set the module registry
     *
     * @param ModuleManagerInterface $registry
     */
    public function setRegistry(ModuleRegistry $registry)
    {
        $this->_registry = $registry;
        return $this;
    }

    /**
     * Returns the module registriy
     *
     * @return \rampage\core\ModuleRegistry
     */
    protected function getRegistry()
    {
        return $this->_registry;
    }

    /**
     * Set the path manager
     *
     * @param PathManager $path
     */
    public function setPathManager(PathManager $path)
    {
        $this->_pathManager = $path;
        return $this;
    }

    /**
     * Path manager instance
     *
     * @return PathManager
     */
    protected function getPathManager()
    {
        if (!$this->_pathManager) {
            $this->setPathManager($this->getRegistry()->getPathManager());
        }

        return $this->_pathManager;
    }

    /**
     * Set loaded flag
     *
     * @param bool $flag
     * @return \rampage\core\Module
     */
    protected function setIsLoaded($flag)
    {
        $this->_isLoaded = (bool)$flag;
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
        if (isset($this->_options['path'])) {
            $path = $this->_options['path'];
        } else {
            if (isset($this->_options['_path'])) {
                $info = new SplFileInfo($this->_options['_path']);
            } else {
                $info = new SplFileInfo($this->getPathManager()->get('modules', $this->_name));
            }

            if ($info->isFile()) {
                $info = new SplFileInfo('phar://' . $info->getPathname());
            }

            if (!$info->isDir()) {
                throw new exception\RuntimeException('Failed to locate module: ' . $this->_name);
            }

            $path = $info->getPathname();
            $this->_options['path'] = $path;
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
     * Is loaded check
     */
    public function isLoaded()
    {
        return $this->_isLoaded;
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

        $this->_isLoaded = true;
        $manifest = $this->getModulePath(static::STATIC_FILE, true);
        if ($manifest->isReadable() && $manifest->isFile()) {
            $data = include $manifest;

            $this->manifest = $data;
            return $this;
        }

        $config = new ManifestConfig($this, $this->getModulePath('module.xml'));
        $this->_manifest = $config->toArray();

        return $this;
    }

    /**
     * Compile manifest
     */
    public function compileManifest($file = null)
    {
        $array = var_export($this->_manifest, true);
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
        return $this->isLoaded() && is_array($this->_manifest);
    }

    /**
     * Check for custom module instance
     *
     * @return bool
     */
    public function hasInstance()
    {
        return (isset($this->_manifest['module_instance']) && $this->_manifest['module_instance']);
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
        if (isset($this->_manifest['module_file'])) {
            return $this->getModulePath($this->_manifest['module_file']);
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

        $this->_manifest['module_file'] = $file;
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
        if ($this->_instance) {
            return $this->_instance;
        }

        if (!$this->hasInstance()) {
            return null;
        }

        $class = str_replace('.', '\\', $this->_manifest['module_instance']);
        $class = '\\' . trim($class, '\\');

        $this->loadModuleClass($class);
        $this->_instance = new $class($this);

        return $this->_instance;
    }

    /**
     * Returns the application config
     *
     * @return array
     */
    public function getConfig()
    {
        $config = $this->_manifest['application_config'];
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
        if (isset($this->_manifest['console']['banner'])) {
            return false;
        }

        return '';
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\ModuleManager\Feature\ConsoleUsageProviderInterface::getConsoleUsage()
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        if (isset($this->_manifest['console']['usage'])) {
            return $this->_manifest['console']['usage'];
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

        return $this->_manifest['autoloader_config'];
    }
}