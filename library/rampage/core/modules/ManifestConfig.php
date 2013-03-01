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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\modules;

use rampage\core\xml\Config;
use rampage\core\xml\SimpleXmlElement;
use DOMDocument;

/**
 * Manifest config
 */
class ManifestConfig extends Config
{
    /**
     * Manifest
     *
     * @var array
     */
    protected $manifest = array();

    /**
     * Current module instance
     *
     * @var \rampage\core\modules\ModuleInterface
     */
    private $module = null;

    /**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::__construct()
     */
    public function __construct(ModuleInterface $module, $file)
    {
        $this->module = $module;
        parent::__construct($file);
    }

    /**
     * Validate manifest xml
     *
     * @return bool
     */
    public function validate($xsd = null)
    {
        if (!$xsd) {
            $xsdPath = (class_exists('Phar', false) && \Phar::running())? \Phar::running() : __DIR__ . '/../../../..';
            $xsd = $xsdPath . '/xsd/rampage/core/ModuleManifest.xsd';
        }

        try {
            $dom = new DOMDocument();
            $dom->loadXML($this->getXml()->asXML());

            $result = $dom->schemaValidate($xsd);
            unset($dom);
        } catch (\Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * Returns the module name
     *
     * @return string
     */
    public function getModuleName()
    {
        return (string)$this->getXml()->module['name'];
    }

    /**
     * Returns the module version
     *
     * @return string
     */
    public function getModuleVersion()
    {
        return (string)$this->getXml()->module['version'];
    }

    /**
     * Module instance
     *
     * @return \rampage\core\modules\ModuleInterface
     */
    protected function getModule()
    {
        return $this->module;
    }

    /**
     * Returns the file path for the current module
     *
     * @param string $file
     * @param bool $asFileInfo
     * @return string|\SplFileInfo
     */
    private function getModulePath($file, $asFileInfo = false)
    {
        return $this->getModule()->getModulePath($file, $asFileInfo);
    }

	/**
     * Map manifest config
     *
     * @param SimpleXmlElement $xml
     * @param string $nodeName
     * @param string $configName
     * @param string $attribute
     */
    protected function mapServiceManifest(SimpleXmlElement $xml, $nodeName, $configName, $attribute)
    {
        if (!$xml->{$nodeName}) {
            return $this;
        }

        foreach ($xml->{$nodeName} as $node) {
            $value = (string)$node[$attribute];
            $name = (string)$node['name'];

            if (!$value || !$name) {
                continue;
            }

            $this->manifest['application_config']['service_manager'][$configName][$name] = $value;
        }

        return $this;
    }

    /**
     * Load service config
     */
    protected function loadServiceConfig()
    {
        $xml = $this->getNode('./services');
        if (!$xml instanceof SimpleXmlElement) {
            return $this;
        }

        $config = &$this->manifest['application_config'];

        foreach ($xml->xpath("./factory[@name != '' and @class != '']") as $factory) {
            $name = (string)$factory['name'];
            $class = (string)$factory['class'];
            $class = trim(strtr($class, '.', '\\'), '\\');
            $configName = ($factory->is('abstract', false))? 'abstract_factories' : 'factories';
            $config['service_manager'][$configName][$name] = $class;
        }

        foreach ($xml->xpath("./share[@name != '']") as $shared) {
            $name = (string)$shared['name'];
            $config['service_manager']['shared'][$name] = $shared->is('shared', true);;
        }

        foreach ($xml->xpath("./initializer[@initializer != '']") as $initNode) {
            $class = (string)$initNode['initializer'];
            $class = trim(strtr($class, '.', '\\'), '\\');

            $config['service_manager']['initializers'][] = $class;
        }

        $this->mapServiceManifest($xml, 'alias', 'aliases', 'aliasto')
             ->mapServiceManifest($xml, 'service', 'invokables', 'class');
//              ->mapServiceManifest($xml, 'initializer', 'initializers', 'initializer', true);

        return $this;
    }

    /**
     * Load locale config
     */
    protected function loadLocaleConfig()
    {
        $xml = $this->getXml();
        $config = &$this->manifest['application_config'];

        if (!isset($xml->locale->pattern)) {
            return $this;
        }

        foreach ($xml->locale->pattern as $node) {
            $dir = isset($node['basedir'])? (string)$node['basedir'] : 'locale';
            $config['translator']['translation_patterns'][] = array(
                'type' => isset($node['type'])? (string)$node['type'] : 'gettext',
                'basedir' => $this->getModulePath($dir),
                'pattern' => isset($node['pattern'])? (string)$node['pattern'] : '%s.mo',
            );
        }

        return $this;
    }

    /**
     * Child node to array
     *
     * @param SimpleXmlElement $xml
     * @param string $name
     * @return array
     */
    protected function childToArray(SimpleXmlElement $xml, $name)
    {
        if (!isset($xml->{$name})) {
            return array();
        }

        return $xml->{$name}->toPhpValue('array');
    }

    /**
     * Extract route config
     *
     * @param SimpleXmlElement $node
     * @return array
     */
    protected function getRouteConfigOptions(SimpleXmlElement $node, $type)
    {
        $config = null;

        if (!isset($node->$type)) {
            return $config;
        }

        $typeNode = $node->$type;
        switch ($type) {
            case 'standard':
                $config = array(
                    'type' => 'rampage.route.standard',
                    'frontname' => (string)$typeNode['frontname'],
                    'namespace' => (string)$typeNode['namespace'],
                    'allowed_params' => array(),
                    'defaults' => $this->childToArray($typeNode, 'defaults'),
                );

                foreach ($typeNode->xpath("./parameters/allow[. != '']") as $allowedParam) {
                    $allowedParam = (string)$allowedParam;
                    $config['allowed_params'][$allowedParam] = $allowedParam;
                }

                break;

            case 'literal':
                $config = array(
                    'type' => 'literal',
                    'route' => (string)$typeNode['route'],
                    'constraints' => $this->childToArray($typeNode, 'constraints'),
                    'defaults' => $this->childToArray($typeNode, 'defaults')
                );

                break;

            case 'segment':
                $config = array(
                    'type' => 'segment',
                    'route' => (string)$typeNode['route'],
                    'constraints' => $this->childToArray($typeNode, 'constraints'),
                    'defaults' => $this->childToArray($typeNode, 'defaults')
                );

                break;

            case 'regex':
                $config = array(
                    'type' => 'regex',
                    'spec' => (string)$typeNode['spec'],
                    'constraints' => $this->childToArray($typeNode, 'constraints'),
                    'defaults' => $this->childToArray($typeNode, 'defaults')
                );

                break;

            case 'layout':
                $config = array(
                    'type' => 'rampage.route.layout',
                    'route' => (string)$typeNode['route'],
                    'layout' => (string)$typeNode['layout'],
                    'handles' => array()
                );

                foreach ($typeNode->xpath("./handle[@name != '']") as $handleNode) {
                    $handle = (string)$handleNode['name'];
                    $config['handles'][$handle] = $handle;
                }

                break;

            case 'custom':
                $config = $typeNode->options->toPhpValue('array');
                break;
        }

        if ($config) {
            $type = $config['type'];
            unset($config['type']);

            $config = array(
                'type' => $type,
                'options' => $config
            );
        }

        return $config;
    }

    /**
     * get route config from manifest xml
     *
     * @param SimpleXmlElement $node
     * @return array|false
     */
    protected function getRouteConfig($node)
    {
        if ((!$node instanceof SimpleXmlElement) || !$node->route) {
            return false;
        }

        $config = array();

        foreach ($node->route as $route) {
            $type = (string)$route['type'];
            $name = (string)$route['name'];
            if (!$name) {
                continue;
            }

            $routeConfig = $this->getRouteConfigOptions($route, $type);
            if (!is_array($routeConfig)) {
                return null;
            }

            $config[$name] = $routeConfig;
            if (isset($route['mayterminate'])) {
                $config[$name]['may_terminate'] = $route->is('mayterminate');
            }

            $children = $this->getRouteConfig($route->routes);
            if (!empty($children)) {
                $config[$name]['child_routes'] = $children;
            }
        }

        return $config;
    }

    /**
     * Load route config from manifest
     */
    protected function loadRouteConfig()
    {
        $xml = $this->getNode('router');
        if (!$xml instanceof SimpleXmlElement) {
            return $this;
        }

        $config = $this->getRouteConfig($xml);
        if (!$config) {
            return $this;
        }

        $this->manifest['application_config']['router']['routes'] = $config;
        return $this;
    }

    /**
     * Load layout config
     */
    protected function loadLayoutConfig()
    {
        $xml = $this->getXml();

        foreach ($xml->xpath("./resources/layout/config[@file != '']") as $node) {
            $file = (string)$node['file'];
            $priority = intval((string)$node['priority']);
            $scope = (string)$node['scope'];

            if (!$scope) {
                $scope = $this->getModuleName();
            }

            $file = $scope . '::' . $file;
            $this->manifest['application_config']['rampage']['layout']['files'][$file] = $priority;
        }

        return $this;
    }

    /**
     * Load theme config
     *
     * @return \rampage\core\modules\ManifestConfig
     */
    protected function loadThemeConfig()
    {
        $xml = $this->getXml();

        foreach ($xml->xpath('./resources/theme[@name != "" and @path != ""]') as $node) {
            $path = (string)$node['path'];
            $name = (string)$node['name'];

            $this->manifest['application_config']['rampage']['themes'][$name] = $path;
        }

        return $this;
    }

    /**
     * Load layout config
     */
    protected function loadResourceConfig()
    {
        $xml = $this->getXml();

        foreach ($xml->xpath("./resources/path[. != '']") as $node) {
            $path = (string)$node;
            $scope = (string)$node['scope'];
            $type = (string)$node['type'];

            if (!$scope) {
                $scope = $this->getModuleName();
            }

            $type = $type?: 'base';
            $this->manifest['application_config']['rampage']['resources'][$scope][$type] = $this->getModulePath($path);
        }

        return $this;
    }

    /**
     * Load packages config
     *
     * @return \rampage\core\modules\ManifestConfig
     */
    protected function loadPackagesConfig()
    {
        $xml = $this->getXml();

        /* @var $child \rampage\core\xml\SimpleXmlElement */
        foreach ($xml->xpath("packages/package[. != '']") as $child) {
            $dir = (isset($child['directory']))? (string)$child['directory'] : 'src';
            $namespace = trim(str_replace('.', '\\', (string)$child), '\\');
            $path = trim($dir, '/');

            if ($child->is('fqpath', false)) {
                $path .= '/' . trim(str_replace('\\', '/', $namespace), '/');
            }

            $this->manifest['autoloader_config']['Zend\Loader\StandardAutoloader']['namespaces'][$namespace] = $this->getModulePath($path);
        }

        foreach ($xml->xpath("packages/aliases/alias[@name != '' and @class != '']") as $alias) {
            $name = (string)$alias['name'];
            $class = (string)$alias['class'];

            $this->manifest['application_config']['packages']['aliases'][$name] = $class;
        }

        return $this;
    }

    /**
     * Load controllers config
     *
     * @return \rampage\core\modules\ManifestConfig
     */
    protected function loadControllersConfig()
    {
        $xml = $this->getXml();

        if (!isset($xml->controllers)) {
            return $this;
        }

        foreach ($xml->controllers as $controllers) {
            $namespace = (string)$controllers['namespace'];
            $prefix = (string)$controllers['prefix'];
            $prefix = ($prefix)?: $namespace;

            if ($namespace) {
                $namespace = trim(strtr($namespace, array('.' => '\\')), '\\')
                           . '\\controllers\\';
            }

            if ($prefix) {
                $prefix = trim(strtr($prefix, array('\\' => '.')), '.') . '.';
            }

            foreach ($controllers->xpath("./controller[@name != '']") as $node) {
                $name = strtr((string)$node['name'], array('\\' => '.'));
                $class = strtr((string)$node, array('.' => '\\'));

                if (empty($class)) {
                    if (strpos($name, '.') !== false) {
                        $parts = explode('.', $name);
                        $last = array_pop($parts);

                        $parts[] = ucfirst($last) . 'Controller';
                        $class = implode('\\', $parts);
                    } else {
                        $class = ucfirst($name) . 'Controller';
                    }
                }

                $name = $prefix . $name;
                $class = $namespace . $class;

                $this->manifest['application_config']['controllers']['invokables'][$name] = $class;
            }
        }

        return $this;
    }

    /**
     * Parse manifest and create the manifest array
     *
     * @return array
     */
    public function toArray()
    {
        $this->manifest = array(
            'application_config' => array(),
            'autoloader_config' => array(),
        );

        $this->loadPackagesConfig()
             ->loadLayoutConfig()
             ->loadResourceConfig()
             ->loadThemeConfig()
             ->loadServiceConfig()
             ->loadLocaleConfig()
             ->loadControllersConfig()
             ->loadRouteConfig();

        return $this->manifest;
    }
}
