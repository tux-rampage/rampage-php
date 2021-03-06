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

namespace rampage\core\view;

use rampage\core\resources\ThemeInterface;
use Zend\View\Resolver\ResolverInterface as ViewResolverInterface;
use Zend\View\Renderer\RendererInterface;

class TemplateLocator implements ViewResolverInterface
{
    /**
     * @var ThemeInterface
     */
    private $theme = null;

    /**
     * @param ThemeInterface $theme
     */
    public function __construct(ThemeInterface $theme)
    {
        $this->theme = $theme;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\View\Resolver\ResolverInterface::resolve()
     */
    public function resolve($name, RendererInterface $renderer = null)
    {
        $name .= '.phtml';
        $file = $this->theme->resolve('template', $name, null, true);

        if (($file !== false) && $file->isFile() && $file->isReadable()) {
            return $file->getPathname();
        }

        // TODO: Fallback - about to be removed
        @list($scope, $path) = explode('/', $name, 2);
        $file = $this->theme->resolve('template', $path, $scope, true);

        if (($file === false) || !$file->isFile() || !$file->isReadable()) {
            return false;
        }

        trigger_error(sprintf('Found resource template "@%s". You should prefix them with "@" since this fallback will be removed in the next release!', $name), E_USER_WARNING);
        return $file->getPathname();
    }
}
