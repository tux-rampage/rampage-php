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

namespace rampage\core;

use DOMDocument;
use ArrayObject;

use rampage\core\xml\XmlConfig;
use rampage\core\xml\SimpleXmlElement;
use rampage\core\services\DIServiceFactory;

/**
 * Manifest config
 */
class ModuleManifest extends XmlConfig
{
    /**
     * Manifest
     *
     * @var ArrayObject
     */
    protected $manifest = null;

    /**
     * Current module instance
     *
     * @var \rampage\core\modules\ModuleInterface
     */
    private $moduleDirectory = null;

    /**
     * @var array
     */
    private $processors = array();

    /**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::__construct()
     */
    public function __construct($moduleDirectory, $file)
    {
        $this->moduleDirectory = rtrim($moduleDirectory, '/') . '/';
        parent::__construct($file);
    }

    /**
     * @param ManifestProcessorInterface $processor
     * @return \rampage\core\ModuleManifest
     */
    public function addIncludeProcessor(ManifestProcessorInterface $processor)
    {
        $this->processors[] = $processor;
        return $this;
    }

    /**
     * @param string $type
     * @return \rampage\core\ManifestProcessorInterface|boolean
     */
    protected function getIncludeProcessor($type)
    {
        /* @var $processor \rampage\core\ManifestProcessorInterface */
        foreach ($this->processors as $processor) {
            if ($processor->isTypeSupported($type)) {
                return $processor;
            }
        }

        return false;
    }

    /**
     * Process include directives
     *
     * @return self
     */
    protected function processIncludes()
    {
        foreach ($this->getXml()->xpath('/includes/include[@type != "" and @file != ""]') as $include) {
            $processor = $this->getIncludeProcessor((string)$include['type']);
            if (!$processor) {
                trigger_error(sprintf('No include processor for "%s" available. Did you forget to register it?', (string)$include['type']), E_USER_WARNING);
                continue;
            }

            $processor->load((string)$include['file'], $this->manifest);
        }

        return $this;
    }

    /**
     * Validate manifest xml
     *
     * @return bool
     */
    public function validate($xsd = null)
    {
        if (!$xsd) {
            $xsdPath = __DIR__ . '/../../../';
            $xsd = $xsdPath . 'xsd/rampage/core/ModuleManifest.xsd';
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
     * Format php class name
     *
     * this will transform dot style class names to namespaced php classes
     *
     * @param string $class
     * @return string
     */
    private function formatClassName($class)
    {
        return strtr((string)$class, '.', '\\');
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
        $path = $this->moduleDirectory . ltrim($file, '/');
        if ($asFileInfo) {
            $path = new \SplFileInfo($path);
        }

        return $path;
    }

	/**
     * Map manifest config
     *
     * @param SimpleXmlElement $xml
     * @param string $nodeName
     * @param string $configName
     * @param string $attribute
     */
    protected function mapServiceManifest(SimpleXmlElement $xml, $nodeName, $configName, $attribute, $key)
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

            $this->manifest['application_config'][$key][$configName][$name] = $value;
        }

        return $this;
    }

    /**
     * DI Config
     */
    protected function loadDiConfig()
    {
        $node = $this->getNode('./services/di');
        if ($node === null) {
            return $this;
        }

        foreach ($node->xpath('./definitions/precompiled[@file != ""]') as $definitionNode) {
            $this->manifest['application_config']['di']['definition']['compiler'][] = $this->getModulePath((string)$definitionNode['file']);
        }

        $instanceConfig = array();

        foreach ($node->xpath('./aliases/alias[@alias != "" and @class != ""]') as $aliasNode) {
            $alias = (string)$aliasNode['alias'];
            $instanceConfig['aliases'][$alias] = (string)$aliasNode['class'];
        }

        foreach ($node->xpath('./preferences/preference[@type != "" and @class != ""]') as $preference) {
            $type = $this->formatClassName($preference['type']);
            $preferredType = $this->formatClassName($preference['class']);
            $instanceConfig['preferences'][$type][] = $preferredType;
        }

        foreach ($node->xpath('./instances/type[@name != ""]') as $typeNode) {
            $name = $this->formatClassName($typeNode['name']);
            if (in_array($name, array('preferences', 'preference', 'alias', 'aliases'))) {
                continue;
            }

            if (isset($typeNode['shared'])) {
                $instanceConfig[$name]['shared'] = $typeNode->toValue('bool', 'shared');
            }

            foreach ($typeNode->xpath('./injections/instance[@method != "" and @class != ""]') as $injectService) {
                $method = (string)$injectService['method'];
                $param = $name . '::' . $method . ':0';
                $instanceConfig[$name]['injections'][$method][$param] = $this->formatClassName($injectService['class']);
            }

            foreach ($typeNode->xpath('./parameters/parameter[@name != ""]') as $parameterNode) {
                $paramName = (string)$parameterNode['name'];
                $value = isset($parameterNode['class'])? $this->formatClassName((string)$parameterNode['class']) : $parameterNode->toPhpValue();

                $instanceConfig[$name]['parameters'][$paramName] = $value;
            }
        }

        if (!empty($instanceConfig)) {
            $this->manifest['application_config']['di']['instance'] = $instanceConfig;
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

        foreach ($xml->xpath('./service[@name != "" and @class != ""]') as $serviceXml) {
            $name = (string)$serviceXml['name'];
            $config['service_manager']['factories'][$name] = new DIServiceFactory((string)$serviceXml['class']);
        }

        $this->mapServiceManifest($xml, 'alias', 'aliases', 'to', 'service_manager')
             ->mapServiceManifest($xml, 'class', 'invokables', 'class', 'service_manager');

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
            $config['translator']['translation_file_patterns'][] = array(
                'type' => isset($node['type'])? (string)$node['type'] : 'gettext',
                'base_dir' => $this->getModulePath($dir),
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

        if (!$config) {
            return false;
        }

        if (isset($typeNode['controller'])) {
            $config['defaults']['controller'] = (string)$typeNode['controller'];
        }

        if (isset($typeNode['action'])) {
            $config['defaults']['action'] = (string)$typeNode['action'];
        }

        $type = $config['type'];
        unset($config['type']);

        $config = array(
            'type' => $type,
            'options' => $config
        );

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
     * Console config
     */
    protected function loadConsoleConfig()
    {
        $xml = $this->getXml();

        $banner = (string)$this->getNode('./console/banner');
        if ($banner) {
            $this->manifest['console']['banner'] = $banner;
        }

        foreach ($xml->xpath('./console/command[@name != "" and @route != "" and @controller != ""]') as $node) {
            $name = (string)$node['name'];
            $config = array(
                'route' => (string)$node['route'],
                'defaults' => (isset($node->defaults))? $node->defaults->toPhpValue('array') : array(),
            );

            $config['defaults']['controller'] = (string)$node['controller'];
            if (isset($node['action'])) {
                $config['defaults']['action'] = (string)$node['action'];
            }

            $this->manifest['application_config']['console']['router']['routes'][$name]['options'] = $config;

            if (isset($node->usage) && isset($node->usage->command) && isset($node->usage->command['command'])) {
                $command = (string)$node->usage->command['command'];
                $this->manifest['console']['usage'][$command] = (string)$node->usage->command;

                foreach ($node->usage->xpath('./parameter[@parameter != ""]') as $param) {
                    $this->manifest['console']['usage'][] = array(
                        (string)$param['parameter'],
                        (string)$param
                    );
                }
            }
        }

        return $this;
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

            $this->manifest['application_config']['rampage']['themes'][$name]['paths'] = $this->getModulePath($path);

            if (isset($node['fallbacks'])) {
                $fallbacks = explode(',', (string)$node['fallbacks']);
                $fallbacks = array_filter(array_map('trim', $fallbacks));

                $this->manifest['application_config']['rampage']['themes'][$name]['fallbacks'] = $fallbacks;
            }
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
        foreach ($xml->xpath("packages/classmap[@file != '']") as $child) {
            $file = $this->getModulePath((string)$child['file'], true);

            if ($file->isFile() && $file->isReadable()) {
                $this->manifest['autoloader_config']['Zend\Loader\ClassMapAutoloader'][] = $file->getPathname();
            }
        }

        /* @var $child \rampage\core\xml\SimpleXmlElement */
        foreach ($xml->xpath("packages/package[. != '' or @name != '']") as $child) {
            $dir = (isset($child['directory']))? (string)$child['directory'] : 'src';
            $namespace = (isset($child['name']))? (string)$child['name'] : (string)$child;
            $namespace = trim(str_replace('.', '\\', $namespace), '\\');
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
            // $prefix = ($prefix)?: $this->getModuleName();

            if ($prefix) {
                // set the namespace accorting to the convention
                if (!$namespace) {
                    $namespace = trim($prefix, '.\\') . '.controllers';
                }

                $prefix = trim(strtr($prefix, array('\\' => '.')), '.') . '.';
            }

            // Format namespace
            $namespace = trim(strtr($namespace, array('.' => '\\')), '\\') . '\\';

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
     * @param SimpleXmlElement $xml
     * @param string $xpath
     * @param string $key
     */
    protected function loadServiceManagerConfig($xpath, $key)
    {
        $xml = $this->getNode($xpath);
        if (!$xml instanceof SimpleXmlElement) {
            return $this;
        }

        $config = &$this->manifest['application_config'];

        foreach ($xml->xpath("./factory[@name != '' and @class != '']") as $factory) {
            $name = (string)$factory['name'];
            $class = (string)$factory['class'];
            $class = trim(strtr($class, '.', '\\'), '\\');
            $configName = ($factory->is('abstract', false))? 'abstract_factories' : 'factories';
            $config[$key][$configName][$name] = $class;
        }

        foreach ($xml->xpath("./share[@name != '']") as $shared) {
            $name = (string)$shared['name'];
            $config[$key]['shared'][$name] = $shared->is('shared', true);;
        }

        foreach ($xml->xpath("./initializer[@initializer != '']") as $initNode) {
            $class = (string)$initNode['initializer'];
            $class = trim(strtr($class, '.', '\\'), '\\');

            $config[$key]['initializers'][] = $class;
        }

        foreach ($xml->xpath('./service[@name != "" and @class != ""]') as $serviceXml) {
            $name = (string)$serviceXml['name'];
            $config[$key]['factories'][$name] = new DIServiceFactory((string)$serviceXml['class']);
        }

        $this->mapServiceManifest($xml, 'alias', 'aliases', 'aliasto', $key)
            ->mapServiceManifest($xml, 'class', 'invokables', 'class', $key);

        return $this;
    }

    /**
     * Parse manifest and create the manifest array
     *
     * @return array
     */
    public function load()
    {
        $this->manifest = new ArrayObject(array(
            'name' => $this->getModuleName(),
            'version' => $this->getModuleVersion(),
            'application_config' => array(),
            'autoloader_config' => array(),
            'console' => array(
                'usage' => array(),
            ),
        ));

        $this->loadPackagesConfig()
             ->loadLayoutConfig()
             ->loadResourceConfig()
             ->loadThemeConfig()
             ->loadServiceConfig()
             ->loadDiConfig()
             ->loadLocaleConfig()
             ->loadControllersConfig()
             ->loadRouteConfig()
             ->loadConsoleConfig();

        $this->loadServiceManagerConfig('./view/helpers', 'view_helper_manager');
        $this->processIncludes();

        return $this->manifest;
    }

    /**
     * Parse manifest and create the manifest array
     *
     * @param string $key
     * @return array
     */
    public function toArray($key = null)
    {
        if ($this->manifest === null) {
            $this->load();
        }

        if ($key !== null) {
            return (isset($this->manifest[$key]))? $this->manifest[$key] : null;
        }

        return $this->manifest->getArrayCopy();
    }
}
