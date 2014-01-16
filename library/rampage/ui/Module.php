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

namespace rampage\ui;

use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ZF2 Module Entryclass
 *
 * This module provides some UI components
 */
class Module implements ViewHelperProviderInterface,
    ControllerPluginProviderInterface
{
    /**
     * @var ToastContainer
     */
    private $toastContainer = null;

    /**
     * Toast container
     */
    protected function getToastContainer()
    {
        if (!$this->toastContainer) {
            $this->toastContainer = new ToastContainer();
        }

        return $this->toastContainer;
    }

    /**
     * @see \Zend\ModuleManager\Feature\ControllerPluginProviderInterface::getControllerPluginConfig()
     */
    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'toast' => function(ServiceLocatorInterface $serviceManager) {
                    $plugin = new ToastControllerPlugin($this->getToastContainer());
                }
            ),
        );
    }

    /**
     * @see \Zend\ModuleManager\Feature\ViewHelperProviderInterface::getViewHelperConfig()
     */
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'toast' => function(ServiceLocatorInterface $serviceManager) {
                    $helper = new ToastViewHelper($this->getToastContainer());
                },
            ),
        );
    }
}
