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
     * @param string $moduleDirectory Absolute path to the module directory
     * @param string $file Absolute path tho manifext xml file
     * @param array|Traversable Additional include processors
     */
    public function __construct($moduleDirectory, $file, $includeProcessors = array())
    {
        $this->moduleDirectory = rtrim($moduleDirectory, '/') . '/';

        foreach ($includeProcessors as $processor) {
            $this->addIncludeProcessor($processor);
        }

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

            $file = $this->getModulePath((string)$include['file']);
            $processor->load($file, $this->manifest);
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
            $xsdPath = __DIR__ . '/../../../xsd/';
            $xsd = $xsdPath . 'rampage/core/ModuleManifest.xsd';
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
     * @deprecated since 1.0.0
     * @param SimpleXmlElement $xml
     * @param string $nodeName
     * @param string $configName
     * @param string $attribute
     */
    protected function mapServiceManifest(SimpleXmlElement $xml, $nodeName, $configName, $attribute, $key)
    {
        return $this;
    }

    /**
     * DI Config
     */
    protected function loadDiConfig()
    {
        $xml = $this->getNode('dicontainer');
        if (!$xml instanceof SimpleXmlElement) {
            return $this;
        }

        foreach ($xml->xpath('./definitions/precompiled[@file != ""]') as $definitionNode) {
            $this->manifest['application_config']['di']['definition']['compiler'][] = $this->getModulePath((string)$definitionNode['file']);
        }

        $instanceConfig = array();

        foreach ($xml->xpath('./instances/instance[@class != "" or @alias != ""]') as $instanceXml) {
            $alias = (string)$instanceXml['alias'];
            $class = (string)$instanceXml['class'];
            $name = $alias?: $class;

            if ($alias && $class) {
                $instanceConfig['aliases'][$alias] = (string)$instanceXml['class'];
            }

            if (isset($instanceXml['shared'])) {
                $instanceConfig[$name]['shared'] = $instanceXml->is('shared');
            }

            foreach ($instanceXml->xpath('./aspreference/for[@class != ""]') as $prefXml) {
                $for = (string)$prefXml['class'];
                $instanceConfig['preferences'][$for][] = $name;
            }

            foreach ($instanceXml->xpath('./parameters/parameter[@key !=""]') as $paramXml) {
                $paramName = (string)$paramXml['key'];
                $instanceConfig[$name]['parameters'][$paramName] = $paramXml->toPhpValue();
            }

            // TODO: Implement injections
//             foreach ($instanceXml->xpath('./injections/inject[@method !=""]') as $injectXml) {
//                 $method = (string)$injectXml['method'];
//             }
        }

        if (!empty($instanceConfig)) {
            $this->manifest['application_config']['di']['instance'] = $instanceConfig;
        }

        return $this;
    }

    /**
     * Load service config
     * @return self
     */
    protected function loadServiceConfig()
    {
        $xml = $this->getNode('./servicemanager');
        $config = $this->createServiceManagerConfig($xml, true);

        if ($config) {
            $this->manifest['application_config']['service_manager'] = $config;
        }

        return $this;
    }

    /**
     * @param string $xml
     * @param bool $diAware
     * @return array|bool
     */
    protected function createServiceManagerConfig($xml, $diAware = false)
    {
        if (!$xml instanceof SimpleXmlElement) {
            return false;
        }

        $config = array();

        foreach ($xml->xpath('./services/service[@name != ""]') as $serviceXml) {
            $name = (string)$serviceXml['name'];
            $classByName = strtr($name, '.', '\\');
            $class = strtr((string)$serviceXml['class'], '.', '\\');

            if (isset($serviceXml->factory) && $serviceXml->factory['class']) {
                // TODO: Implement factory delegate to respect options array
                $factory = (string)$serviceXml->factory['class'];
                $config['factories'][$name] = $factory;
            } else if ($class) {
                $useDi = (isset($serviceXml['usedi']))? $serviceXml->is('usedi') : true;

                if ($diAware && $useDi) {
                    $config['factories'][$name] = new DIServiceFactory($class);
                } else {
                    $config['invokables'][$name] = $class;
                }
            }

            if (isset($serviceXml['shared'])) {
                $config['shared'][$name] = $serviceXml->is('shared');
            }

            foreach ($serviceXml->xpath('./aliases/alias[@name != ""]') as $aliasXml) {
                $alias = (string)$aliasXml['name'];
                $config['aliases'][$alias] = $name;
            }

            if (!$diAware || !isset($serviceXml->di)) {
                continue;
            }

            $hasDiPreferences = false;
            foreach ($serviceXml->xpath('./di/provides[@class != ""]') as $diPrefXml) {
                $preferFor = strtr((string)$diPrefXml['class'], '.', '\\');
                $hasDiPreferences = true;

                $this->manifest['application_config']['di']['instance']['preferences'][$preferFor] = $name;
            }

            $diClass = strtr((string)$serviceXml->di['class'], '.', '\\');
            if (!$diClass && $hasDiPreferences) {
                $diClass = $class;
            }

            if ($diClass && ($diClass != $classByName)) {
                $this->manifest['application_config']['di']['instance']['aliases'][$name] = $diClass;
            }
        }

        foreach ($xml->xpath("./factories/factory[@class != '']") as $factoryXml) {
            $factory = strtr((string)$factoryXml['class'], '.', '\\'); // TODO: Implement factory delegate to respect options
            $config['abstract_factories'][] = $factory;
        }

        return (!empty($config))? $config : false;
    }

    /**
     * Plugin manager configs
     */
    protected function loadPluginManagerConfigs()
    {
        foreach ($this->getXml()->xpath('plugins/pluginmanager[@name != ""]') as $pmConfig) {
            $key = (string)$pmConfig;
            $config = $this->createServiceManagerConfig($pmConfig);

            if ($config) {
                $this->manifest['application_config'][$key] = $config;
            }
        }

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

        foreach ($xml->xpath('locale/pattern[@pattern != ""]') as $node) {
            $dir = isset($node['basedir'])? (string)$node['basedir'] : 'locale';

            $config['translator']['translation_file_patterns'][] = array(
                'type' => isset($node['type'])? (string)$node['type'] : 'php',
                'base_dir' => $this->getModulePath($dir),
                'pattern' => isset($node['pattern'])? (string)$node['pattern'] : '%s.php',
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

        $routeType = $type;
        $typeNode = $node->$type;

        switch ($type) {
            case 'standard':
                $routeType = 'rampage.route.standard';
                $config = array(
                    'frontname' => (string)$typeNode['frontname'],
                    'namespace' => (string)$typeNode['namespace'],
                    'allowed_params' => array(),
                    'defaults' => $this->childToArray($typeNode, 'defaults'),
                );

                foreach ($typeNode->xpath("./parameters/allow[@name != '']") as $allowedParam) {
                    $allowedParam = (string)$allowedParam['name'];
                    $config['allowed_params'][$allowedParam] = $allowedParam;
                }

                break;

            case 'literal':
                $config = array(
                    'route' => (string)$typeNode['route'],
                    'constraints' => $this->childToArray($typeNode, 'constraints'),
                    'defaults' => $this->childToArray($typeNode, 'defaults')
                );

                break;

            case 'segment':
                $config = array(
                    'route' => (string)$typeNode['route'],
                    'constraints' => $this->childToArray($typeNode, 'constraints'),
                    'defaults' => $this->childToArray($typeNode, 'defaults')
                );

                break;

            case 'regex':
                $config = array(
                    'spec' => (string)$typeNode['spec'],
                    'constraints' => $this->childToArray($typeNode, 'constraints'),
                    'defaults' => $this->childToArray($typeNode, 'defaults')
                );

                break;

            case 'layout':
                $routeType = 'rampage.route.layout';
                $config = array(
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
            default:
                $typeNode = $node->custom;
                $config = (isset($typeNode->options))? $typeNode->options->toPhpValue('array') : null;

                if (isset($config['type'])) {
                    $routeType = $config['type'];
                    unset($config['type']);
                }

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

        $config = array(
            'type' => $routeType,
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

        foreach ($xml->xpath('./resources/themes/theme[@name != "" and @path != ""]') as $node) {
            $path = (string)$node['path'];
            $name = (string)$node['name'];

            $this->manifest['application_config']['rampage']['themes'][$name]['path'] = $this->getModulePath($path);

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

        foreach ($xml->xpath("./resources/paths/path[@path != '']") as $node) {
            $path = (string)$node['path'];
            $scope = (string)$node['scope'];
            $type = (string)$node['type'];

            if (!$scope) {
                $scope = $this->getModuleName();
            }

            $type = $type? : 'base';
            $this->manifest['application_config']['rampage']['resources'][$scope][$type] = $this->getModulePath($path);
        }

        return $this;
    }

    /**
     * @return self
     */
    protected function loadClassConfig()
    {
        $xml = $this->getXml();

        /* @var $child \rampage\core\xml\SimpleXmlElement */
        foreach ($xml->xpath("classes/classmaps/classmap[@file != '']") as $child) {
            $file = $this->getModulePath((string)$child['file'], true);

            if ($file->isFile() && $file->isReadable()) {
                $this->manifest['autoloader_config']['Zend\Loader\ClassMapAutoloader'][] = $file->getPathname();
            }
        }

        /* @var $child \rampage\core\xml\SimpleXmlElement */
        foreach ($xml->xpath('classes/namespaces/namespace[@namespace != "" and @path != ""]') as $child) {
            $relative = $child->is('relative');
            $dir = (string)$child['path'];
            $namespace = strtr((string)$child['namespace'], '.', '\\');

            if ($dir == '.') {
                $dir = '';
            }

            if (!$relative) {
                $dir .= '/' . trim(strtr($namespace, '\\', '/'), '/');
            }

            $this->manifest['autoloader_config']['Zend\Loader\StandardAutoloader']['namespaces'][$namespace] = $this->getModulePath($dir);
        }

        return $this;
    }

    /**
     * Load controllers config
     *
     * @deprecated
     * @return \rampage\core\modules\ManifestConfig
     */
    protected function loadControllersConfig()
    {
        return $this;
    }

    /**
     * @deprecated
     * @param SimpleXmlElement $xml
     * @param string $xpath
     * @param string $key
     */
    protected function loadServiceManagerConfig($xpath, $key)
    {
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

        $this->loadClassConfig()
             ->loadLayoutConfig()
             ->loadResourceConfig()
             ->loadThemeConfig()
             ->loadServiceConfig()
             ->loadPluginManagerConfigs()
             ->loadLocaleConfig()
             ->loadRouteConfig()
             ->loadDiConfig();

//              ->loadDiConfig()
//              ->loadConsoleConfig();

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
