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

namespace rampage\core\view\helper;

use Zend\View\HelperPluginManager;
use Zend\ServiceManager\ConfigInterface;
use rampage\core\ObjectManagerInterface;

/**
 * Plugin manager
 */
class PluginManager extends HelperPluginManager
{
    /**
     * Object manager
     *
     * @var \rampage\core\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * (non-PHPdoc)
     * @see \Zend\View\HelperPluginManager::__construct()
     */
    public function __construct(ObjectManagerInterface $objectManager, ConfigInterface $configuration = null)
    {
        $this->invokableClasses = array_merge($this->invokableClasses, array(
            'resourceurl' => 'rampage.core.view.helper.ResourceUrl',
            'url' => 'rampage.core.view.helper.Url'
        ));

        $this->objectManager = $objectManager;
        parent::__construct($configuration);
    }

    /**
     * Object manager
     *
     * @return \rampage\core\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\AbstractPluginManager::createFromInvokable()
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        if (!$this->getObjectManager()) {
            return parent::createFromInvokable($canonicalName, $requestedName);
        }

        $invokable = $this->invokableClasses[$canonicalName];
        if (is_array($this->creationOptions) && !empty($this->creationOptions)) {
            $instance = $this->getObjectManager()->get($invokable, array('options' => $this->creationOptions));
        } else {
            $instance = $this->getObjectManager()->get($invokable);
        }

        return $instance;
    }
}