<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\core\controllers;

use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\MvcEvent;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\Console\ColorInterface;

/**
 * Module compiler
 */
class ModuleCompilerController extends AbstractController
{
    /**
     * {@inheritdoc}
     * @see \Zend\Mvc\Controller\AbstractController::onDispatch()
     */
    public function onDispatch(MvcEvent $e)
    {
        $console = $this->getServiceLocator()->get('Console');
        if (!$console instanceof ConsoleAdapterInterface) {
            throw new \DomainException('Could not find console service. Are you sure this was invoked from command line?');
        }

        $manager = $this->getServiceLocator()->get('ModuleManager');
        if (!$manager instanceof ModuleManager) {
            $console->write('Could not find module manager - exit!' . "\n", ColorInterface::RED);
            return;
        }

        foreach ($manager->getLoadedModules() as $name => $module) {
            if (!is_callable(array($module, 'compile'))) {
                continue;
            }

            $console->write(sprintf('Compiling module "%s" ...' . "\n", $name));
            $module->compile();
        }

        $console->write('Done!' . "\n\n", ColorInterface::LIGHT_GREEN);
    }
}
