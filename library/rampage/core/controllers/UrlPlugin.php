<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2012 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 * @version   $Id$
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