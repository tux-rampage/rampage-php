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
            if (!isset($conf['paths'])) {
                continue;
            }

            $theme->addLocation($name, $conf['paths']);
        }

        return $this;
    }

    /**
     * @param Theme $theme
     * @param UserConfigInterface $userConfig
     * @return Theme
     */
    protected function prepareThemeFallbacks(Theme $theme, UserConfigInterface $userConfig, $config)
    {
        $name = $userConfig->getConfigValue('design.theme.name');
        if (!$name) {
            return;
        }

        $config = new DesignConfig($config);
        $theme->setCurrentTheme($name);

        // Build fallback paths
        // fb1 -> fb2 -> ... -> fbX

        $fallbacks = $config->getFallbackThemes($name);
        $fallback = null;
        $last = null;

        foreach ($fallbacks as $fallbackName) {
            $current = clone $theme;
            $current->setCurrentTheme($fallbackName);

            // Define first as fallback path entry
            if (!$fallback) {
                $fallback = $current;
            }

            // build the fallback path
            if ($last) {
                $last->setFallback($current);
            }

            $last = $current;
        }

        if ($fallback) {
            $theme->setFallback($fallback);
        }

        return $theme;
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

        if (isset($config['rampage']['themes'])) {
            $this->addThemes($theme, $config['rampage']['themes']);
        }

        if ($userConfig instanceof UserConfigInterface) {
            $this->prepareThemeFallbacks($theme, $userConfig, $config);
        }

        return $theme;
    }
}
