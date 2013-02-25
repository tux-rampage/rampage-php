<?php
/**
 * This is part of application_name
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
 * @package   package_name
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\view\http;

use Zend\Mvc\MvcEvent;
use rampage\core\view\Layout;
use rampage\core\resource\FileLocatorInterface;

/**
 * Application bootstrap listener
 */
class LayoutConfigListener
{
    /**
     * Add files
     *
     * @param \rampage\core\view\Layout $layout
     * @param array $data
     * @return \rampage\core\view\listener\LayoutConfigListener
     */
    protected function addFiles(Layout $layout, array $data)
    {
        $config = $layout->getUpdate()->getConfig();
        foreach ($data as $file => $priority) {
            $config->addFile($file, $priority);
        }

        return $this;
    }

    /**
     * Invoke listener
     *
     * @param MvcEvent $event
     */
    public function __invoke(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();
        $config = $serviceManager->get('Config');
        $layout = $serviceManager->get('rampage.Layout');
        $theme = $serviceManager->get('rampage.Theme');

        if (!($layout instanceof Layout) || !($theme instanceof FileLocatorInterface)) {
            return $this;
        }

        if (isset($config['rampage']['layout']['files']) && is_array($config['rampage']['layout']['files'])) {
            $this->addFiles($layout, $config['rampage']['layout']['files']);
        }

        return true;
    }
}