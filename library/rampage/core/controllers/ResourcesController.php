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

namespace rampage\core\controllers;

use rampage\core\resources\PublishingStrategyInterface;
use rampage\core\exception\RuntimeException;
use rampage\core\Logger;
use rampage\core\ConsoleLogWriter;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Log\LoggerAwareInterface;
use Zend\Console\Console;

/**
 * Resources controller
 */
class ResourcesController extends AbstractActionController
{
    /**
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $theme = $this->params('theme');
        $scope = $this->params('scope');
        $file = $this->params('file');

        // TODO: Implement file resolver
        die('TODO: Implement ' . __METHOD__);
    }

    /**
     * @throws RuntimeException
     * @throws \BadMethodCallException
     */
    public function publishAction()
    {
        $strategy = $this->getServiceLocator()->get('rampage.ResourcePublishingStrategy');
        if (!$strategy instanceof PublishingStrategyInterface) {
            throw new RuntimeException('No resource publishing strategy available!');
        }

        if (!Console::isConsole()) {
            throw new \BadMethodCallException('This action is available for console, only.');
        }

        if ($strategy instanceof LoggerAwareInterface) {
            $logger = new Logger();
            $writer = new ConsoleLogWriter();

            $writer->setConsoleAdapter($this->getServiceLocator()->get('console'));
            $logger->addWriter($writer);

            $strategy->setLogger($logger);
        }

        $strategy->publish();
    }
}
