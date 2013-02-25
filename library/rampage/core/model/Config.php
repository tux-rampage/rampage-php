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
use rampage\core\xml\mergerule\UniqueAttributeRule;

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
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::initMergeRules()
     */
    protected function initMergeRules()
    {
        $this->getMergeRules()->add(new UniqueAttributeRule('~/property$~', 'name'));
        return $this;
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
     * Get a property name
     *
     * @param string $name
     * @return mixed
     */
    public function getConfigValue($name, $default = null)
    {
        if (array_key_exists($name, $this->values)) {
            return $this->values[$name];
        }

        $node = $this->getNode("./property[@name = $name]");
        if (!$node) {
            return $default;
        }

        $type = (string)$node['type'];
        switch ($type) {
            case 'array':
            case 'instance':
                $value = $node->toPhpValue($type);
                break;

            default:
                $value = $node->toValue($type, 'value');
                break;
        }

        $this->values[$name] = $value;
        return $value;
    }

    /**
     * Configure the given url model
     *
     * @param Url $url
     */
    public function configureUrlModel(Url $url)
    {
        if ($unsecure = $this->getConfigValue('web.baseurl')) {
            $url->setBaseUrl($unsecure, false);
        }

        if ($secure = $this->getConfigValue('web.baseurl.secure')) {
            $url->setBaseUrl($secure, true);
        } else if ($unsecure) {
            $url->setBaseUrl($unsecure, true);
        }

        return $this;
    }
}