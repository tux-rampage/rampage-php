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

use rampage\core\PathManager;
use rampage\core\xml\Config as XmlConfig;
use rampage\core\xml\SimpleXmlElement;
use rampage\core\xml\mergerule\UniqueAttributeRule;
use rampage\core\xml\mergerule\AllowSiblingsRule;
use rampage\orm\exception\RuntimeException;

/**
 * Adapter config implemetnation
 */
class AdapterConfig extends XmlConfig implements AdapterConfigInterface
{
    /**
     * Adapter option mapping
     *
     * @var array
     */
    protected $optionMap = array(
        'driver' => 'string',
        'hostname' => 'string',
        'port' => 'int',
        'username' => 'string',
        'password' => 'string',
        'database' => 'string',
        'charset' => 'string',
        'platform' => 'string',
        'platform_options' => 'array'
    );

    /**
     * Path manager instance
     *
     * @var \rampage\core\PathManager
     */
    private $pathManager = null;

    /**
     * Alias resolver cache
     *
     * @var array
     */
    protected $aliases = array();

    /**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::__construct()
     */
    public function __construct(PathManager $pathManager)
    {
        $this->pathManager = $pathManager;
    }

    /**
     * Path manager instance
     *
     * @return \rampage\core\PathManager
     */
    public function getPathManager()
    {
        return $this->pathManager;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::initMergeRules()
     */
    protected function initMergeRules()
    {
        $this->getMergeRules()
            ->add(new UniqueAttributeRule('~/adapter$~', 'name'))
            ->add(new AllowSiblingsRule('~/item$~'));

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::_init()
     */
    protected function _init()
    {
        $this->_file = $this->getPathManager()->get('etc', 'database.xml');
        return parent::_init();
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\db\AdapterConfigInterface::getAdapterOptions()
     */
    public function getAdapterOptions($name)
    {
        $name = $this->xpathQuote($name);
        $node = $this->getNode("adapter[@name='$name']");
        $options = array(
            'driver' => 'Pdo_Mysql',
            'hostname' => 'localhost'
        );

        if (!$node instanceof SimpleXmlElement) {
            return $options;
        }

        foreach ($this->optionMap as $option => $type) {
            if (!isset($node->$option)) {
                continue;
            }

            $options[$option] = $node->{$option}->toPhpValue($type);
        }

        return $options;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\db\AdapterConfigInterface::hasAdapterConfig()
     */
    public function hasAdapterConfig($name)
    {
        $node = $this->getNode("adapter[@name='$name']");
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
        $node = $this->getNode("adapter[@name = $quoted]");
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
            $node = $this->getNode("adapter[@name = $quoted]");
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
}