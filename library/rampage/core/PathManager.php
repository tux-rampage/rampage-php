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

use rampage\core\Utils;
use rampage\core\pathmanager\FallbackInterface;

/**
 * Path manager
 */
class PathManager
{
    /**
     * Path items
     *
     * @var array
     */
    private $paths = array();

    /**
     * Constructor
     *
     * @param string $config
     */
    public function __construct($config = null)
    {
        if ($config && is_string($config) && !is_dir($config) && is_readable($config)) {
            $config = parse_ini_file($config, false);
        } else if (is_string($config) && is_dir($config) && !$this->has('root')) {
            $this->set('root', $config);
        }

        if (!$this->has('root')) {
            $this->set('root', getcwd());
        }

        if (!is_array($config) && !($config instanceof \Traversable)) {
            $file = $this->prepare('{{root_dir}}/environment.conf');
            if (is_readable($file)) {
                $config = parse_ini_file($file, false);
            }
        }

        $defaults = array(
            'app' => '{{root_dir}}/application',
            'public' => '{{root_dir}}/public',
            'var' => '{{root_dir}}/var',
            'cache' => '{{var_dir}}/cache',
            'etc' => '{{app_dir}}/etc',
            'modules' => array('{{root_dir}}/modules'),
            'media' => '{{public_dir}}/media'
        );

        if (is_array($config) || ($config instanceof \Traversable)) {
            foreach ($config as $type => $path) {
                $this->set($type, $path);
                unset($defaults[$type]);
            }
        }

        foreach ($defaults as $type => $path) {
            $this->set($type, $path);
        }
    }

    /**
     * Prepare path
     *
     * @param string $path
     * @return string
     */
    protected function prepare($path)
    {
        foreach ($this->paths as $type => $pathValue) {
            if ($pathValue instanceof FallbackInterface) {
                $pathValue = $pathValue->resolve('');
            }

            $path = str_replace('{{'.$type.'_dir}}', $pathValue, $path);
        }

        return $path;
    }

    /**
     * Returns a path
     *
     * @param string $type
     * @param string $file
     */
    public function get($type, $file = null)
    {
        if (!isset($this->paths[$type])) {
            $method = 'get'.Utils::camelize($type).'Dir';

            if (!method_exists($this, $method)) {
                return null;
            }

            $this->set($type, $this->$method());
        }

        $path = $this->paths[$type];
        if ($file) {
            if ($path instanceof pathmanager\FallbackInterface) {
                $path = $path->resolve($file);
            } else {
                $path .= '/' . ltrim($file, '/');
            }
        }

        return $path;
    }

    /**
     * Set path
     *
     * @param string $type
     * @param string $path
     */
    public function set($type, $path)
    {
        if ($path instanceof pathmanager\FallbackInterface) {
            $this->paths[$type] = $path;
            return $this;
        }

        if (is_array($path)) {
            if (isset($this->paths[$type])) {
                $fallback = $this->paths[$type];
                if (!$fallback instanceof pathmanager\FallbackInterface) {
                    $first = $fallback;
                    $fallback = new pathmanager\DefaultFallback();
                    $fallback->addPath($first);
                }
            } else {
                $fallback = new pathmanager\DefaultFallback();
            }

            $this->paths[$type] = $fallback;
            foreach ($path as $item) {
                $this->set($type, $item);
            }

            return $this;
        }

        $path = $this->prepare($path);

        if (isset($this->paths[$type]) && ($this->paths[$type] instanceof pathmanager\FallbackInterface)) {
            $this->paths[$type]->addPath($path);
        } else {
            $this->paths[$type] = $path;
        }

        return $this;
    }

    /**
     * Check if path is registered
     *
     * @param string $type
     * @return bool
     */
    public function has($type)
    {
        return isset($this->paths[$type]);
    }
}