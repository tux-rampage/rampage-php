<?php
/**
 * Copyright (c) 2014 Axel Helmert
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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\tool;

use rampage\io\ConsoleIO;

use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\Console\ColorInterface;

use Zend\Mvc\Controller\AbstractActionController;

use UnexpectedValueException;
use Zend\View\Model\ConsoleModel;


class CreateSkeletonController extends AbstractActionController
{
    /**
     * {@inheritdoc}
     * @see \Zend\Mvc\Controller\AbstractController::onDispatch()
     */
    public function createAction()
    {
        $console = $this->getServiceLocator()->get('console');

        if (!$console instanceof ConsoleAdapterInterface) {
            throw new \RuntimeException('Invalid controller invocation');
        }

        $io = new ConsoleIO($console);
        $skeleton = new ProjectSkeleton($io);
        $skeleton->addComponent(new DirectoryLayoutComponent())
            ->addComponent(new BootstrapGeneratorComponent());

        $console->writeLine('Application Skeleton Generator' . PHP_EOL, ColorInterface::LIGHT_CYAN);
        $console->writeLine('Creating application skeleton:' . PHP_EOL, ColorInterface::GREEN);

        $skeleton->create($this->params()->fromRoute());

        $console->writeLine(PHP_EOL . 'Done!' . PHP_EOL, ColorInterface::LIGHT_GREEN);
    }

    /**
     * @return \Zend\View\Model\ConsoleModel
     */
    public function usageAction()
    {
        $renderer = new ConsoleHelpRenderer();
        $console = $this->getServiceLocator()->get('console');
        $moduleManager = $this->getServiceLocator()->get('ModuleManager');
        $model = new ConsoleModel();

        $model->setResult($renderer->renderHelp($console, $moduleManager));
        return $model;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        return $this->usageAction();
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Mvc\Controller\AbstractActionController::notFoundAction()
     */
    public function notFoundAction()
    {
        return $this->usageAction();
    }
}
