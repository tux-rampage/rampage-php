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

namespace rampage\core\view\http;

use rampage\core\view\Layout;
use rampage\core\Application;

use Zend\Mvc\MvcEvent;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Renderer\RendererInterface;
use Zend\Http\Response;
use Zend\Http\Request;
use rampage\core\view\LayoutAwareInterface;

/**
 * Default Render strategy
 */
class DefaultRenderStrategy implements ListenerAggregateInterface
{
    /**
     * Layout to render for page not found
     */
    const ERROR_NOTFOUND_LAYOUT = 'error.404';

    /**
     * Layout to render for internal server error
     */
    const ERROR_EXCEPTION_LAYOUT = 'error.500';

    /**
     * Registered listeners
     *
     * @var array
     */
    protected $listeners = array();

    /**
     * (non-PHPdoc)
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'render'), 100);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'injectLayout'), 100);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'dispatchError'), 100);

        return $this;
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\EventManager\ListenerAggregateInterface::detach()
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }

        return $this;
    }

    /**
     * Render
     *
     * @param MvcEvent $event
     */
    public function render(MvcEvent $event)
    {
        $result = $event->getResult();
        $request = $event->getRequest();
        $response = $event->getResponse();
        $application = $event->getApplication();

        if ((!$result instanceof Layout)
          || (!$response instanceof Response)
          || (!$request instanceof Request)) {
            return $this;
        }

        $renderer = $application->getServiceManager()->get('rampage.core.view.http.Renderer');
        if (!$renderer instanceof RendererInterface) {
            return;
        }

        $result->setRequest($request)
               ->setResponse($response);

        $response->setContent($renderer->render($result));

        // Now change the result to response to prevend ZF's renderer to be applied
        $event->setResult($response);
        $event->setParam('layout', $result); // keep a reference to the layout
    }

    /**
     * Dispatch error
     *
     * @param MvcEvent $event
     */
    public function dispatchError(MvcEvent $event)
    {
        $error = $event->getError();
        if (empty($error)) {
            return;
        }

        /* @var $layout \rampage\core\view\Layout */
        $layout = $event->getApplication()->getServiceManager()->get('rampage.Layout');
        $response = $event->getResponse();
        $data = $layout->getData();

        if (!$response) {
            $response = new Response();
            $event->setResponse($response);
        }

        $data['error_reason'] = $error;

        switch ($error) {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
            case Application::ERROR_CONTROLLER_INVALID:
            case Application::ERROR_ROUTER_NO_MATCH:
                $layout->load(static::ERROR_NOTFOUND_LAYOUT);
                $response->setStatusCode(404);
                break;

            case Application::ERROR_EXCEPTION:
            default:
                $data['exception'] = $event->getParam('exception');

                $layout->load(static::ERROR_EXCEPTION_LAYOUT);
                $response->setStatusCode(500);
                break;
        }

        $event->stopPropagation(true);
        $event->setResult($layout);
    }

	/**
     * Inject layout instance
     *
     * @param MvcEvent $event
     */
    public function injectLayout(MvcEvent $event)
    {
        if (!$event->getTarget() instanceof LayoutAwareInterface) {
            return;
        }

        $layout = $event->getApplication()->getServiceManager()->get('rampage.Layout');
        $event->getTarget()->setLayout($layout);
    }
}