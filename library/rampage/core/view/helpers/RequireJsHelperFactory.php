<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2015 LUKA netconsult GmbH (www.luka.de)
 */

namespace rampage\core\view\helpers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class RequireJsHelperFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $helper = new RequireJsHelper();

        if (isset($config['requirejs']['modules'])
            && (is_array($config['requirejs']['modules'])
                || ($config['requirejs']['modules'] instanceof \Traversable))) {

            foreach ($config['requirejs']['modules'] as $module => $location) {
                $helper->addModule($module, $location);
            }
        }

        return $helper;
    }
}
