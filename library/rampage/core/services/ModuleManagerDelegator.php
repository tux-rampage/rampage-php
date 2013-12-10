<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2013 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\core\services;

use rampage\core\PathManager;
use rampage\core\modules\EventConfigModuleListener;

use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\ModuleEvent;

use Traversable;

/**
 * Does not really create a delegator, but ensures the module definition is loaded as
 */
class ModuleManagerDelegator implements DelegatorFactoryInterface
{
    /**
     * @param string $name
     * @return string
     */
    protected function formatModuleName($name)
    {
        $name = strtr($name, '.', '\\');
        return $name;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\ServiceManager\DelegatorFactoryInterface::createDelegatorWithName()
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        /* @var $instance \Zend\ModuleManager\ModuleManager */
        $instance = call_user_func($callback);
        $pathManager = $serviceLocator->has('PathManager')? $serviceLocator->get('PathManager') : null;

        if ((!$pathManager instanceof PathManager) || (!$instance instanceof ModuleManager)) {
            return $instance;
        }

        $existing = array_map(array($this, 'formatModuleName'), $instance->getModules());
        $modconf = $pathManager->get('etc', 'modules.conf.php');
        $modules = (is_file($modconf))? include $modconf : null;

        if (is_array($modules) || ($modules instanceof Traversable)) {
            foreach ($modules as $moduleName) {
                $moduleName = $this->formatModuleName($moduleName);

                if (in_array($moduleName, $existing)) {
                    continue;
                }

                $existing[] = $moduleName;
            }
        }

        $event = new EventConfigModuleListener($serviceLocator);

        $instance->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, $event);
        $instance->setModules($existing);
        return $instance;
    }
}
