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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\db;

// XML dependencies
use rampage\core\xml\SimpleXmlElement;
use rampage\core\xml\mergerule\UniqueAttributeRule;
use rampage\core\xml\mergerule\AllowSiblingsRule;
use rampage\core\modules\AggregatedXmlConfig;

use rampage\orm\exception\RuntimeException;
use rampage\orm\db\platform\ServiceLocator as PlatformServiceLocator;
use rampage\orm\db\platform\FieldMapper;
use rampage\orm\db\platform\PlatformInterface;

use Zend\Stdlib\Hydrator\HydratorInterface;
use rampage\core\ObjectManagerInterface;
use rampage\core\PathManager;
use rampage\core\ModuleRegistry;
use Zend\Stdlib\Hydrator\StrategyEnabledInterface;

/**
 * Database config implementation
 */
class Config extends AggregatedXmlConfig implements adapter\ConfigInterface, platform\ConfigInterface
{
    /**
     * Adapter option mapping
     *
     * @var array
     */
    protected $optionMap = array(
        'hostname' => 'string',
        'port' => 'int',
        'username' => 'string',
        'password' => 'string',
        'database' => 'string',
        'charset' => 'string',
    );

    /**
     * Alias resolver cache
     *
     * @var array
     */
    protected $aliases = array();

    /**
     * Object manager
     *
     * @var \rampage\core\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * (non-PHPdoc)
     * @see \rampage\core\modules\AggregatedXmlConfig::__construct()
     */
    public function __construct(ObjectManagerInterface $objectManager, ModuleRegistry $registry, PathManager $pathManager)
    {
        $this->objectManager = $objectManager;
        parent::__construct($registry, $pathManager);
    }

    /**
     * @return \rampage\core\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\modules\AggregatedXmlConfig::getGlobalFilename()
     */
    protected function getGlobalFilename()
    {
        return 'database.xml';
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\core\modules\AggregatedXmlConfig::getModuleFilename()
     */
    protected function getModuleFilename()
    {
        return 'etc/database.xml';
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::getDefaultMergeRulechain()
     */
    protected function getDefaultMergeRulechain()
    {
        $rules = parent::getDefaultMergeRulechain();
        $rules->add(new UniqueAttributeRule('~/(adapter|platform|entity|attribute)$~', 'name'))
              ->add(new UniqueAttributeRule('~/hydrator$~', 'class'))
              ->add(new AllowSiblingsRule('~/item$~'));

        return $rules;
    }

    /**
     * Extracts platform specific options from the given node
     *
     * @param SimpleXmlElement $node
     * @return array|null
     */
    protected function getPlatformOptions(SimpleXmlElement $node)
    {
        $platform = $this->xpathQuote((string)$node['platform']);
        $options = null;

        $optionNode = $node->xpath("./platform[@name = $platform]/options")->current();
        if ($optionNode instanceof SimpleXmlElement) {
            $options = $optionNode->toPhpValue('array');
        }

        return $options;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\AdapterConfigInterface::getAdapterOptions()
     */
    public function getAdapterOptions($name)
    {
        $name = $this->xpathQuote($name);
        $node = $this->getNode("./adapters/adapter[@name = $name]");
        $options = array(
            'driver' => 'Pdo_Mysql',
            'hostname' => 'localhost'
        );

        if (!$node instanceof SimpleXmlElement) {
            return $options;
        }

        if (isset($node->driveroptions)) {
            $options += $node->driveroptions->toPhpValue('array');
        }

        if (isset($node['driver'])) {
            $options['driver'] = (string)$node['driver'];
        }

        foreach ($this->optionMap as $option => $type) {
            if (!isset($node->$option)) {
                continue;
            }

            $options[$option] = $node->{$option}->toPhpValue($type);
        }

        if (isset($node->initsql)) {
            $options['initsql'] = array();
            foreach ($node->initsql as $sql) {
                $sql = (string)$sql;

                if ($sql) {
                    $options['initsql'][] = $sql;
                }
            }
        }

        if (isset($node['platform'])) {
            $options['platform'] = (string)$node['platform'];
            $platformOptions = $this->getPlatformOptions($node);

            if ($platformOptions) {
                $options['platform_options'];
            }
        } else if (strtolower($options['driver']) == 'pdo_oci') {
            // ZF" does not recognize oracle platform for PDO_OCI
            $options['platform'] = 'Oracle';
        }

        return $options;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\AdapterConfigInterface::hasAdapterConfig()
     */
    public function hasAdapterConfig($name)
    {
        $name = $this->xpathQuote($name);
        $node = $this->getNode("./adapters/adapter[@name = $name]");
        return ($node instanceof SimpleXmlElement);
    }

    /**
     * Follow the use references
     *
     * @param string $name
     * @param array $stack
     * @return string
     */
    protected function resolveUseReference($name, array &$stack = array())
    {
        $quoted = $this->xpathQuote($name);
        $node = $this->getNode("./adapters/adapter[@name = $quoted]");
        $stack[$name] = $name;

        while (($node instanceof SimpleXmlElement) && isset($node['use'])) {
            $name = (string)$node['use'];
            if (isset($stack[$name])) {
                throw new RuntimeException('Circular DB reference: ' . implode(' -> ', $stack) . ' -> ' . $name);
            }

            if ($name == 'default') {
                break;
            }

            $stack[$name] = $name;
            $quoted = $this->xpathQuote($name);
            $node = $this->getNode("./adapters/adapter[@name = $quoted]");
        }

        return $name;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\AdapterConfigInterface::resolveAdapterAlias()
     */
    public function resolveAdapterAlias($name)
    {
        if ($name == 'default') {
            return $name;
        }

        if (isset($this->aliases[$name])) {
            return $this->aliases[$name];
        }

        $stack = array();
        $requested = $name;
        $name = $this->resolveUseReference($name, $stack);

        // If this adapter does not exist - fall back to default (read/write)
        if (!$this->hasAdapterConfig($name)) {
            $name = 'default';

            if ((substr($name, 4) == 'read') && $this->hasAdapterConfig('default.read')) {
                $name = $this->resolveUseReference('default.read', $stack);
            } else if ((substr($name, 5) == 'write') && $this->hasAdapterConfig('default.write')) {
                $name = $this->resolveUseReference('default.write', $stack);
            }

            // Pass two default.read and default.write could point to nowhere
            if (($name != 'default') && !$this->hasAdapterConfig($name)) {
                $name = 'default';
            }
        }

        $this->aliases[$requested] = $name;
        return $name;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\ConfigInterface::configurePlatformServiceLocator()
     */
    public function configurePlatformServiceLocator(PlatformServiceLocator $locator)
    {
        foreach ($this->getXml()->xpath('./platforms/platform[@name != "" and @class != ""]') as $node) {
            $locator->setServiceClass((string)$node['name'], (string)$node['class']);
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\ConfigInterface::configureFieldMapper()
     */
    public function configureFieldMapper(FieldMapper $mapper, PlatformInterface $platform, $resource)
    {
        $platformName = $this->xpathQuote($platform->getName());
        $resourceName = $this->xpathQuote($resource);

        // Defaults
        $xpath = "./platforms/defaults/entity[@name = $resourceName]/attribute[@name != '' and @field != '']";
        foreach ($this->getXml()->xpath($xpath) as $node) {
            $mapper->add((string)$node['name'], (string)$node['field']);
        }

        // Platform specific
        $xpath = "./platforms/platform[@name = $platformName]/entity[@name = $resourceName]/attribute[@name != '' and @field != '']";
        foreach ($this->getXml()->xpath($xpath) as $node) {
            $mapper->add((string)$node['name'], (string)$node['field']);
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\ConfigInterface::configureHydrator()
     */
    public function configureHydrator(HydratorInterface $hydrator, PlatformInterface $platform, $resource)
    {
        if (!$hydrator instanceof StrategyEnabledInterface) {
            return $this;
        }

        $platformName = $this->xpathQuote($platform->getName());
        $resourceName = $this->xpathQuote($resource);
        $xpath = "./platforms/platform[@name = $platformName]/entity[@name = $resourceName]/hydrator/attribute[@name != '' and @strategy != '']";
        $di = $this->getObjectManager();

        /* @var $node \rampage\core\xml\SimpleXmlElement */
        foreach ($this->getXml()->xpath($xpath) as $node) {
            $strategy = $di->newInstance((string)$node['strategy'], $node->toPhpValue('array', $di));
            $hydrator->addStrategy((string)$node['name'], $strategy);
        }

        $xpath = "./platforms/defaults/entity[@name = $resourceName]/hydrator/attribute[@name != '' and @strategy != '']";
        foreach ($this->getXml()->xpath($xpath) as $node) {
            $name = (string)$node['name'];
            if ($hydrator->hasStrategy($name)) {
                continue;
            }

            $strategy = $di->newInstance((string)$node['strategy'], $node->toPhpValue('array', $di));
            $hydrator->addStrategy($name, $strategy);
        }


        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\ConfigInterface::getHydratorClass()
     */
    public function getHydratorClass(PlatformInterface $platform, $resource)
    {
        $platformName = $this->xpathQuote($platform->getName());
        $resourceName = $this->xpathQuote($resource);
        $node = $this->getNode("./platforms/platform[@name = $platformName]/entity[@name = $resourceName]/hydrator[@class != '']");

        if (!$node instanceof SimpleXmlElement) {
            $node = $this->getNode("./platforms/defaults/entity[@name = $resourceName]/hydrator[@class != '']");
            if (!$node instanceof SimpleXmlElement) {
                return false;
            }
        }

        return (string)$node['class'];
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\ConfigInterface::getTable()
     */
    public function getTable(PlatformInterface $platform, $resource)
    {
        $platformName = $this->xpathQuote($platform->getName());
        $resourceName = $this->xpathQuote($resource);
        $node = $this->getNode("./platforms/platform[@name = $platformName]/entity[@name = $resourceName and @table != '']");

        if (!$node instanceof SimpleXmlElement) {
            $node = $this->getNode("./platforms/defaults/entity[@name = $resourceName and @table != '']");
            if (!$node instanceof SimpleXmlElement) {
                return false;
            }
        }

        return (string)$node['table'];
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\ConfigInterface::getSequenceName()
     */
    public function getSequenceName(PlatformInterface $platform, $resource)
    {
        $platformName = $this->xpathQuote($platform->getName());
        $resourceName = $this->xpathQuote($resource);
        $node = $this->getNode("./platforms/platform[@name = $platformName]/entity[@name = $resourceName and @sequence != '']");

        if (!$node instanceof SimpleXmlElement) {
            $node = $this->getNode("./platforms/defaults/entity[@name = $resourceName and @sequence != '']");
            if (!$node instanceof SimpleXmlElement) {
                return null;
            }
        }

        return (string)$node['sequence'];
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\ConfigInterface::getConstraintMapperClass()
     */
    public function getConstraintMapperClass(PlatformInterface $platform)
    {
        $name = $platform->getName();
        $quotedName = $this->xpathQuote($name);
        $node = $this->getNode("./platforms/platform[@name = $quotedName]/constraint[@mapper != '']");

        if (!$node instanceof SimpleXmlElement) {
            $node = $this->getNode("./platforms/defaults/constraint[@mapper != '']");
            if (!$node instanceof SimpleXmlElement) {
                return null;
            }
        }

        return (string)$node['mapper'];
    }
}
