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

namespace rampage\core\controllers;

use rampage\core\url\UrlModelLocator;
use Zend\Mvc\Controller\Plugin\Url as ZendUrlPlugin;
use Zend\Mvc\Exception;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventInterface;

/**
 * Url plugin that'll make use of the URL model
 */
class UrlPlugin extends ZendUrlPlugin
{
    /**
     * @var \Zend\Mvc\Router\RouteMatch
     */
    private $routeMatch;

    /**
     * @var \Zend\Mvc\Router\RouteStackInterface
     */
    private $router;

    /**
     * @var UrlModelLocator
     */
    private $urlModelLocator = null;

    /**
     * @param UrlModelLocator $locator
     */
    public function __construct(UrlModelLocator $locator)
    {
        $this->urlModelLocator = $locator;
    }

    /**
     * @throws Exception\DomainException
     * @return \Zend\Mvc\Router\RouteStackInterface
     */
    protected function getRouter()
    {
        if ($this->router) {
            return $this->router;
        }

        $controller = $this->getController();
        if (!$controller instanceof InjectApplicationEventInterface) {
            throw new Exception\DomainException('Url plugin requires a controller that implements InjectApplicationEventInterface');
        }

        $event = $controller->getEvent();
        if ($event instanceof MvcEvent) {
            $this->router = $event->getRouter();
            $this->routeMatch = $event->getRouteMatch();
        } else if ($event instanceof EventInterface) {
            $this->router = $event->getParam('router', false);
            $this->routeMatch = $event->getParam('route-match');
        }

        return $this->router;
    }

    /**
     * @return \Zend\Mvc\Router\RouteMatch
     */
    protected function getRouteMatch()
    {
        $this->getRouter();
        return $this->routeMatch;
    }

    /**
     * @see \Zend\Mvc\Controller\Plugin\Url::fromRoute()
     * @return \Zend\Uri\Http
     */
    public function fromRoute($name = null, array $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        if (!$this->urlModelLocator || !$this->urlModelLocator->has('base')) {
            return parent::fromRoute($name, $params, $options, $reuseMatchedParams);
        }

        $urlModel = $this->urlModelLocator->get('base');
        if ($name === null) {
            return $urlModel->getUrl();
        }

        if ((func_num_args() == 3) && is_bool($options)) {
            // to meet this check for num args in parent method imeplementation
            $reuseMatchedParams = $options;
            $options = array();
        }

        $options['only_return_path'] = true;
        $router = $this->getRouter();

        // set base url to '' since the url model will take care of it
        if ($router instanceof TreeRouteStack) {
            $oldBaseUrl = $router->getBaseUrl();
            $router->setBaseUrl('');
        }

        $url = parent::fromRoute($name, $params, $options, $reuseMatchedParams);
        $urlOptions = (is_array($options))? $options : array();
        $match = $this->getRouteMatch();

        // Restore original base url
        if ($router instanceof TreeRouteStack) {
            $router->setBaseUrl($oldBaseUrl);
        }

        if ($match) {
            $routeMatchParams = $match->getParams();
            $urlOptions = array_merge($match->getParams(), $urlOptions);
        }

        $uri = $urlModel->getUrl($url, $urlOptions);
        return $uri;
    }
}