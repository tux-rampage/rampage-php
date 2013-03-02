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

namespace rampage\core\model\design;

use Zend\Mvc\MvcEvent;
use rampage\core\resource\Theme;
use rampage\core\model\Config as UserConfig;

/**
 * Bootstrap listener
 */
class BootstrapListener
{
    /**
     * Theme
     *
     * @var Theme
     */
    private $theme = null;

    /**
     * App config
     *
     * @var UserConfig
     */
    private $userConfig = null;

    /**
     * Construct
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Theme $theme, UserConfig $userConfig)
    {
        $this->theme = $theme;
        $this->userConfig = $userConfig;
    }

    /**
     * @return \rampage\core\resource\Theme
     */
    protected function getTheme()
    {
        return $this->theme;
    }

    /**
     * @return \rampage\core\model\Config
     */
    protected function getConfig()
    {
        return $this->userConfig;
    }

    /**
     * Invoke listener
     *
     * @param MvcEvent $event
     */
    public function __invoke(MvcEvent $event)
    {
        $name = $this->getConfig()->getConfigValue('design.theme.name');
        if (!$name) {
            return;
        }

        $theme = $this->getTheme();
        $theme->setCurrentTheme($name);
    }
}