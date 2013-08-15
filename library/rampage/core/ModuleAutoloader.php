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

namespace rampage\core;

use Zend\Loader\SplAutoloader;

/**
 * Module autoloader
 */
class ModuleAutoloader implements SplAutoloader
{
    /**
     * @var PathManager
     */
    protected $pathManager = null;

    /**
     * @var array
     */
    protected $subdirs = array();

    /**
     * @param PathManager $pathManager
     */
    public function __construct($options = null)
    {
        $this->setOptions($options);
    }

    /**
     * @param PathManager $pathManager
     * @return \rampage\core\ModuleAutoloader
     */
    public function setPathManager(PathManager $pathManager)
    {
        $this->pathManager = $pathManager;
        return $this;
    }

    /**
     * Add additional subdirectories within the module directory that should be searched for Module.php
     *
     * @param array $directories
     * @return self
     */
    public function addSubdirectories(array $directories)
    {
        foreach ($directories as $dir) {
            $this->subdirs[$dir] = $dir;
        }

        return $this;
    }

    /**
     * @see \Zend\Loader\SplAutoloader::autoload()
     */
    public function autoload($class)
    {
        if (!$this->pathManager || (substr($class, -7) != '\Module')) {
            return;
        }

        $parts = explode('\\', $class);
        array_pop($parts);
        $name = implode('.', $parts);
        $path = $this->pathManager->get('modules', $name . '/Module.php');

        if (is_file($path) && is_readable($path)) {
            include $path;
            return;
        }

        // Walk sub directories
        foreach ($this->subdirs as $dir) {
            $path = $this->pathManager->get('modules', "$name/$dir/Module.php");
            if (is_file($path) && is_readable($path)) {
                include $path;

                if (class_exists($class, false)) {
                    break;
                }
            }
        }
    }

    /**
     * @see \Zend\Loader\SplAutoloader::register()
     */
    public function register()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Following options are allowed
     *
     * pathmanager:
     *     An instance of rampage\core\PathManager
     *
     * subdirectories:
     *     You may provide sub-directories within the module directory that should be searched for Module.php
     *
     * @see \Zend\Loader\SplAutoloader::setOptions()
     * @return self
     */
    public function setOptions($options)
    {
        if (!is_array($options) || !($options instanceof \Traversable)) {
            return $this;
        }

        foreach ($options as $name => $option) {
            switch (strtolower(strtr($name, '-', ''))) {
                case 'pathmanager':
                    $this->setPathManager($option);
                    break;

                case 'subdirectories':
                    if (is_array($option)) {
                        $this->addSubdirectories($option);
                    }

                    break;
            }
        }

        return $this;
    }
}
