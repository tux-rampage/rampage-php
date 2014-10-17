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

namespace rampage\core\view\helpers;

use rampage\core\url\UrlModelLocator;
use Zend\View\Helper\Url as DefaultUrlHelper;
use Zend\Mvc\Router\Http\TreeRouteStack;

/**
 * Advanced URL helper to build FQ URLs
 */
class UrlHelper extends DefaultUrlHelper
{
    /**
     * URL model locator
     *
     * @var \rampage\core\url\UrlModelLocator
     */
    protected $urlModelLocator = null;

    /**
     * Returns the URL model
     *
     * @param UrlModelLocator $modelLocator
     * @param DefaultUrlHelper $parent The URL helper to proxy
     */
    public function __construct(DefaultUrlHelper $wrappedHelper, UrlModelLocator $modelLocator)
    {
        $this->urlModelLocator = $modelLocator;

        // copy dependencies
        $this->routeMatch = $wrappedHelper->routeMatch;
        $this->router = $wrappedHelper->router;
        $this->view = $wrappedHelper->view;
    }

    /**
     * Url model
     *
     * @return \rampage\core\url\UrlModelInterface
     */
    protected function getUrlModel()
    {
        return $this->urlModelLocator->get('base');
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\View\Helper\Url::__invoke()
     */
    public function __invoke($name = null, $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        if ($name === null) {
            return $this->getUrlModel()->getUrl();
        }

        if ((func_num_args() == 3) && is_bool($options)) {
            // to meet this check for num args in parent method imeplementation
            $reuseMatchedParams = $options;
            $options = array();
        }

        $options['only_return_path'] = true;

//         // set base url to '' since the url model will take care of it
//         if ($this->router instanceof TreeRouteStack) {
//             $oldBaseUrl = $this->router->getBaseUrl();
//             $this->router->setBaseUrl('');
//         }

        $url = parent::__invoke($name, $params, $options, $reuseMatchedParams);
        $urlOptions = (is_array($options))? $options : array();
        $match = $this->routeMatch;

//         // Restore original base url
//         if ($this->router instanceof TreeRouteStack) {
//             $this->router->setBaseUrl($oldBaseUrl);
//         }

        if ($match) {
            $routeMatchParams = $match->getParams();
            $urlOptions = array_merge($routeMatchParams, $urlOptions);
        }

        $urlOptions['extractBasePath'] = true;
        $uri = $this->getUrlModel()->getUrl($url, $urlOptions);

        return $uri;
    }
}
