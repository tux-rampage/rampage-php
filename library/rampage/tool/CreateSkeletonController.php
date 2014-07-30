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

use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\MvcEvent;

use UnexpectedValueException;


class CreateSkeletonController extends AbstractController
{
    /**
     * {@inheritdoc}
     * @see \Zend\Mvc\Controller\AbstractController::onDispatch()
     */
    public function onDispatch(MvcEvent $event)
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
        $console->writeLine('Creating Application Skeleton ...');

        $skeleton->create($this->params());

        $console->writeLine('Done' . PHP_EOL);
    }
}
