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

namespace rampage\core\model;

use rampage\core\modules\AggregatedXmlConfig;
use rampage\core\exception\InvalidArgumentException;
use rampage\core\xml\SimpleXmlElement;
use rampage\core\model\config\PropertyMergeRule;

/**
 * Application config
 */
class Config extends AggregatedXmlConfig
{
    /**
     * Config values
     *
     * @var array
     */
    protected $values = array();

    /**
     * Requested but undefined config values cache
     *
     * @var array
     */
    protected $undefined = array();

    /**
     * Current domain for retrieving the config values
     *
     * @var string
     */
    private $domain = null;

    /**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::getDefaultMergeRulechain()
     */
    protected function getDefaultMergeRulechain()
    {
        $rules = parent::getDefaultMergeRulechain();
        $rules->add(new PropertyMergeRule('~/property$~'));

        return $rules;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\modules\AggregatedXmlConfig::getGlobalFilename()
     */
    protected function getGlobalFilename()
    {
        return 'config.xml';
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\core\modules\AggregatedXmlConfig::getModuleFilename()
     */
    protected function getModuleFilename()
    {
        return 'etc/config.xml';
    }

    /**
     * Domain to use for retrieving config
     *
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Current domain for retrieving values
     *
     * @return string
     */
    protected function getDomain()
    {
        if ($this->domain !== null) {
            return $this->domain;
        }

        if (PHP_SAPI == 'cli') {
            $this->domain = '__CLI__';
            return $this->domain;
        }

        $this->domain = isset($_SERVER['SERVER_NAME'])? $_SERVER['SERVER_NAME'] : 'default';
        return $this->domain;
    }

    /**
     * Get a property name
     *
     * @param string $name
     * @return mixed
     */
    public function getConfigValue($name, $default = null, $domain = null)
    {
        if ($domain === null) {
            $domain = $this->getDomain();
        } else if ($domain === false) {
            $domain = '__default__';
        }

        $domain = strtolower($domain);

        if (isset($this->values[$domain]) && array_key_exists($name, $this->values[$domain])) {
            return $this->values[$domain][$name];
        }

        // already tried to fetch this property without success
        if (isset($this->undefined[$domain][$name])) {
            return $default;
        }

        // Find the matching node
        $xpathName = $this->xpathQuote($name);
        if ($domain == '__default__') {
            $node = $this->getNode("./property[@name = $xpathName and not(@domain)]");
        } else {
            $xpathDomain = $this->xpathQuote($domain);
            $node = $this->getNode("./property[@name = $xpathName and @domain = $xpathDomain]");

            if (!$node instanceof SimpleXmlElement) {
                $node = $this->getNode("./property[@name = $xpathName and not(@domain)]");
            }
        }

        // node was not found?
        if (!$node instanceof SimpleXmlElement) {
            $this->undefined[$domain][$name] = true;
            return $default;
        }

        // extract the node value by type
        $type = (string)$node['type'];
        switch ($type) {
            case 'array':
            case 'instance':
                $value = $node->toPhpValue($type);
                break;

            case '':
                $type = 'string';
                // break intentionally omitted

            default:
                $value = $node->toValue($type, 'value');
                break;
        }

        $this->values[$domain][$name] = $value;
        return $value;
    }

    /**
     * Replace placeholders with config values
     *
     * @param string $value
     * @param string $prefix
     */
    protected function processConfigVariables($value, $prefix = null)
    {
        if ($prefix && !preg_match('~^[a-z0-9._-]*$~i', $prefix)) {
            throw new InvalidArgumentException('Invalid config var prefix: ' . $prefix);
        }

        if ($prefix) {
            // dots should be literal in regex
            $prefix = strtr($prefix, '.', '\\.');
        }

        $config = $this;
        $value = preg_replace_callback("~{{({$prefix}[a-z0-9._-]+)}}~i", function($match) use ($config) {
            return (string)$config->getConfigValue($match[1], '');
        }, $value);

        return $value;
    }

    /**
     * Configure the given url model
     *
     * @param Url $url
     */
    public function configureUrlModel(Url $url)
    {
        $property = ($url->getType()?: 'baseurl');

        if ($unsecure = $this->getConfigValue('web.url.unsecure.' . $property)) {
            $unsecure = $this->processConfigVariables($unsecure, 'web.url.');
            $url->setBaseUrl($unsecure, false);
        }

        if ($secure = $this->getConfigValue('web.url.secure.' . $property)) {
            $secure = $this->processConfigVariables($secure, 'web.url.');
            $url->setBaseUrl($secure, true);
        } else if ($unsecure) {
            $url->setBaseUrl($unsecure, true);
        }

        return $this;
    }
}