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

namespace rampage\core\routers\http;

use rampage\core\exception\InvalidArgumentException;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Mvc\Router\Http\RouteMatch;

/**
 * Standard router
 */
class LayoutRoute implements RouteInterface
{
    /**
     * Literal route
     *
     * @var string
     */
    protected $route = null;

    /**
     * Layout name
     *
     * @var array
     */
    protected $layout = null;

    /**
     * Additional handles
     *
     * @var array
     */
    protected $handles = array();

    /**
     * Construct
     */
    public function __construct($route, $layout, $handles = null)
    {
        if (!$layout) {
            throw new InvalidArgumentException('Layout name must not be empty!');
        }

        if (!$route) {
            throw new InvalidArgumentException('Route must not be empty');
        }

        $this->route = $route;
        $this->layout = $layout;

        if (is_array($handles)) {
            $this->handles = $handles;
        }
    }

    /**
     * Check array for values
     *
     * @param array $data
     * @param array $keys
     */
    private static function arrayHasKeys(array $data, array $keys)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create instance as defined by RouteInterface
     *
     * @param array $options
     * @return \rampage\core\router\http\StandardRoute
     */
    public static function factory($options = array())
    {
        if (!is_array($options)
          || !self::arrayHasKeys($options, array('route', 'layout'))) {
            throw new InvalidArgumentException('Invalid router options specified.');
        }

        $handles = (isset($options['handles']))? $options['handles'] : null;;
        return new static($options['route'], $options['layout'], $handles);
    }


    /**
     * (non-PHPdoc)
     * @see \Zend\Mvc\Router\Http\RouteInterface::getAssembledParams()
     */
    public function getAssembledParams()
    {
        return array();
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Mvc\Router\RouteInterface::assemble()
     */
    public function assemble(array $params = array(), array $options = array())
    {
        return $this->route;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Mvc\Router\RouteInterface::match()
     */
    public function match(RequestInterface $request, $pathOffset = null)
    {
        if (!method_exists($request, 'getUri')) {
            return null;
        }

        /* @var $request \Zend\Http\PhpEnvironment\Request */
        $uri  = $request->getUri();
        $path = $uri->getPath();

        if ($path != $this->route) {
            return null;
        }

        $params['controller'] = 'rampage.core.layoutonly';
        $params['layout'] = $this->layout;
        $params['handles'] = $this->handles;

        return new RouteMatch($params, strlen($this->route));
    }
}