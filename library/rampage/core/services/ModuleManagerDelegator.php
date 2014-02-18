<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2013 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\core\services;

use rampage\core\ModuleManagerListenerAggregate;

use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\Loader\AutoloaderFactory;

use Traversable;


/**
 * Does not really create a delegator, but ensures the module definition is loaded as
 */
class ModuleManagerDelegator implements DelegatorFactoryInterface
{
    /**
     * Register module autoloader
     */
    private function registerAutoloader(ServiceLocatorInterface $serviceLocator)
    {
        AutoloaderFactory::factory(array(
            'rampage\core\ModuleAutoloader' => array(
                'pathmanager' => $serviceLocator->get('PathManager'),
                'subdirectories' => array('src')
            )
        ));
    }

    /**
     * {@inheritdoc}
     * @see \Zend\ServiceManager\DelegatorFactoryInterface::createDelegatorWithName()
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        /* @var $instance \Zend\ModuleManager\ModuleManager */
        $instance = call_user_func($callback);

        if (!$instance instanceof ModuleManager) {
            return $instance;
        }

        $this->registerAutoloader($serviceLocator);

        $listenerAggregate = new ModuleManagerListenerAggregate($serviceLocator);
        $instance->getEventManager()->attachAggregate($listenerAggregate);

        return $instance;
    }
}
