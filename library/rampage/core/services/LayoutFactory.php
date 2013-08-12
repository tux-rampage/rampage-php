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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\services;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use rampage\core\view\Layout;

/**
 * Layout factory
 */
class LayoutFactory implements FactoryInterface
{
    /**
     * @param \rampage\core\view\Layout $layout
     * @param array $data
     * @return self
     */
    protected function addLayoutFiles(Layout $layout, array $data)
    {
        $config = $layout->getUpdate()->getConfig();
        foreach ($data as $file => $priority) {
            $config->addFile($file, $priority);
        }

        return $this;
    }

    /**
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $layout = $serviceLocator->get('di')->newInstance('rampage\core\view\Layout');

        if (isset($config['rampage']['layout']['files']) && is_array($config['rampage']['layout']['files'])) {
            $this->addFiles($layout, $config['rampage']['layout']['files']);
        }

        return $layout;
    }
}
