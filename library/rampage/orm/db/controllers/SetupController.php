<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2012 Axel Helmert
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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\db\controllers;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;

use rampage\orm\repository\InstallableRepositoryInterface;
use rampage\orm\exception\RuntimeException;

/**
 * Controller for running ddl setup
 */
class SetupController extends AbstractActionController
{
    /**
     * Repo manager
     *
     * @return \rampage\orm\RepositoryManager
     */
    protected function getRepositoryManager()
    {
        return $this->getServiceLocator()->get('RepositoryManager');
    }

    /**
     * Perform repo schema installs
     */
    public function installAction()
    {
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('This action can only be performed on console');
        }

        $manager = $this->getRepositoryManager();

        foreach ($manager->getRepositoryNames() as $name) {
            $repository = $manager->get($name);
            if (!$repository instanceof InstallableRepositoryInterface) {
                continue;
            }

            echo 'Setup for repository: ', $repository->getName(), ' ...', "\n";
            $repository->setup();
        }

        return 'all done!' . "\n";
    }
}