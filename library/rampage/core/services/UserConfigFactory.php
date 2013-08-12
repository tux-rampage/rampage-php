<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2012 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 * @version   $Id$
 */

namespace rampage\core\services;

use rampage\core\UserConfig;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserConfigFactory implements FactoryInterface
{
    /**
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $pathManager = $serviceLocator->get('rampage.PathManager');
        $instance = new UserConfig();

        $instance->addFile($pathManager->get('etc', 'userconfig.xml'), -100);

        if (isset($config['config']['userconfigs'])
          && (is_array($config['config']['userconfigs'])
          || ($config['config']['userconfigs'] instanceof \Traversable))) {
            foreach ($config['config']['userconfigs'] as $file => $prio) {
                if (!is_string($file)) {
                    $file = $prio;
                    $prio = 1;
                }

                $instance->addFile($file, $prio);
            }
        }

        return $instance;
    }
}
