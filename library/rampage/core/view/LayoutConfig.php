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

namespace rampage\core\view;

use rampage\core\xml\Config;
use rampage\core\xml\mergerule\AllowSiblingsRule;
use rampage\core\xml\mergerule\UniqueAttributeRule;
use rampage\core\exception\InvalidArgumentException;
use Zend\Stdlib\PriorityQueue;
use rampage\core\resource\FileLocatorInterface;

/**
 * Layout config
 */
class LayoutConfig extends Config
{
    /**
     * Priority list
     *
     * @var \Zend\Stdlib\PriorityQueue
     */
    protected $files = null;

    /**
     * Resource locator
     *
     * @var \rampage\core\resource\FileLocatorInterface
     */
    private $resourceLocator = null;

    /**
     * Construct
     *
     * @param \rampage\core\resource\FileLocatorInterface $resourceLocator
     */
    public function __construct(FileLocatorInterface $resourceLocator)
    {
        $this->files = new PriorityQueue();
        $this->resourceLocator = $resourceLocator;
    }

    /**
     * Returns the resource locator
     *
     * @return \rampage\core\resource\FileLocatorInterface
     */
    protected function getResourceLocator()
    {
        return $this->resourceLocator;
    }

    /**
     * Returns all files to load
     *
     * @return \Zend\Stdlib\PriorityQueue
     */
    protected function getFiles()
    {
        return $this->files;
    }

    /**
     * Add a file
     *
     * @param string $file
     * @param int $priority
     */
    public function addFile($file, $priority = 100)
    {
        $file = (string)$file;
        if (!$file) {
            throw new InvalidArgumentException('$file must not be empty!');
        }

        $this->getFiles()->insert($file, $priority);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::_init()
     */
    protected function _init()
    {
        $this->setXml('<?xml version="1.0" encoding="UTF-8"?><layout></layout>');
        $config = new Config();

        foreach ($this->getFiles() as $file) {
            $info = $this->getResourceLocator()->resolve('layout', $file, null, true);
            if (!($info instanceof \SplFileInfo) || !$info->isFile() || !$info->isReadable()) {
                continue;
            }

            $config->loadFile($info->getPathname());
            $this->merge($config, true);
        }

        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::getDefaultMergeRulechain()
     */
    protected function getDefaultMergeRulechain()
    {
        $rules = parent::getDefaultMergeRulechain();
        $rules->add(new AllowSiblingsRule('~/(view|action|reference|remove|update|item)$~'))
              ->add(new AllowSiblingsRule('~/reference/data$~'))
              ->add(new UniqueAttributeRule('~^[^/]+/handle$~', 'name', true));

        return $rules;
    }

    /**
     * Returns the XML node for the requested handle
     *
     * @param string $name
     * @return \rampage\core\xml\SimpleXmlElement
     */
    public function getHandle($name)
    {
        $name = $this->xpathQuote($name);
        return $this->getNode('./handle[@name=' . $name . ']');
    }

    /**
     * Returns a copy of the current instance
     *
     * @return \rampage\core\view\LayoutConfig
     */
    public function copy()
    {
        $copy = clone $this;
        $copy->setXml(clone $this->getXml());

        return $copy;
    }
}