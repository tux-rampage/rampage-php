<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2015 LUKA netconsult GmbH (www.luka.de)
 */

namespace rampage\core\view\helpers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use rampage\core\ArrayConfig;


class RequireJsHelperFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $helper = new RequireJsHelper();

        if (!isset($config['requirejs'])) {
            return $helper;
        }

        $config = new ArrayConfig($config['requirejs']);

        foreach ($config->getSection('modules') as $module => $location) {
            $helper->addModule($module, $location);
        }

        foreach ($config->getSection('bundles') as $bundle => $deps) {
            $helper->addBundle($bundle, $deps);
        }

        foreach ($config->getSection('packages') as $name => $package) {
            if (is_string($package)) {
                $location = (string)$package;
                $main = null;
            } else {
                $location = $package->get('location');
                $main = $package->get('main');
            }

            $helper->addPackage($name, $location, $main);
        }


        return $helper;
    }
}
