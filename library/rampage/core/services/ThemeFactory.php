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
use rampage\core\resources\Theme;
use rampage\core\UserConfigInterface;
use rampage\core\resources\DesignConfig;

class ThemeFactory implements FactoryInterface
{
    /**
     * Add config themes
     *
     * @param Theme $theme
     * @param array $config
     * @return self
     */
    protected function addThemes(Theme $theme, $config)
    {
        if (!is_array($config) && !($config instanceof \Traversable)) {
            return $this;
        }

        foreach ($config as $name => $conf) {
            if (!isset($conf['path'])) {
                continue;
            }

            $theme->addLocation($name, $conf['path']);
        }

        return $this;
    }

    /**
     * @param Theme $theme
     * @param array $config
     * @return self
     */
    protected function prepareThemeFallbacks(Theme $theme, $config)
    {
        $config = new DesignConfig($config);
        $theme->setDesignConfig($config);

        return $this;
    }

    /**
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $userConfig = $serviceLocator->get('UserConfig');
        $pathManager = $serviceLocator->get('rampage.PathManager');
        $fallback = $serviceLocator->get('rampage.ResourceLocator');

        $theme = new Theme($pathManager, $fallback);
        $name = $userConfig->getConfigValue('design.theme.name');

        if (isset($config['rampage']['themes'])) {
            $this->addThemes($theme, $config['rampage']['themes']);
        }

        if ($name) {
            $theme->setCurrentTheme($name);
        }

        $this->prepareThemeFallbacks($theme, $config);
        return $theme;
    }
}
