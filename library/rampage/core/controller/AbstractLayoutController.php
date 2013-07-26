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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\controller;

use rampage\core\exception;
use rampage\core\view\LayoutAwareInterface;
use rampage\core\view\Layout;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Abstract layout controller
 *
 * @method \rampage\core\controller\UrlPlugin url()
 */
abstract class AbstractLayoutController extends AbstractActionController implements LayoutAwareInterface
{
    /**
     * Layout instance
     *
     * @var \rampage\core\view\Layout
     */
    private $layout = null;

    /**
     * (non-PHPdoc)
     * @see \rampage\core\view\LayoutAwareInterface::setLayout()
     */
    public function setLayout(Layout $layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Layout instance
     *
     * @return \rampage\core\view\Layout
     */
    protected function getLayout()
    {
        if (!$this->layout) {
            throw new exception\DependencyException('Missing layout instance');
        }

        return $this->layout;
    }
}