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

use Zend\Mvc\Exception;
use Zend\Mvc\MvcEvent;

/**
 * Layout only controller
 */
class LayoutOnlyController extends AbstractLayoutController
{
	/**
     * (non-PHPdoc)
     * @see \Zend\Mvc\Controller\AbstractActionController::onDispatch()
     */
    public function onDispatch(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if (!$routeMatch) {
            throw new Exception\DomainException('Missing route matches; unsure how to retrieve layout');
        }

        $layout = $this->getLayout();
        $name = $routeMatch->getParam('layout');
        $handles = $routeMatch->getParam('handles');

        if (!$layout) {
            throw new Exception\DomainException('Missing layout name');
        }

        if (is_array($handles)) {
            $layout->getUpdate()->add($handles);
        }

        $layout->load($name);
        $e->setResult($layout);

        return $layout;
    }
}