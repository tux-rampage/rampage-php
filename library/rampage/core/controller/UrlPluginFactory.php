<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2012 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 * @version   $Id$
 */

namespace rampage\core\controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Url plugin factory
 */
class UrlPluginFactory implements FactoryInterface
{
    /**
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        $plugin = new UrlPlugin();
        if ($serviceLocator->has('ObjectManager')) {
            $plugin->setUrlModel($serviceLocator->get('ObjectManager')->get('rampage.core.model.Url'));
        }

        return $plugin;
    }
}