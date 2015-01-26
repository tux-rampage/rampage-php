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

use rampage\core\BaseUrl;
use rampage\core\GracefulArrayAccess;

use Zend\View\Helper\Url as DefaultUrlHelper;


/**
 * Advanced URL helper to build FQ URLs
 */
class UrlHelper extends DefaultUrlHelper
{
    /**
     * @var BaseUrl
     */
    protected $baseUrl;

    /**
     * Returns the URL model
     *
     * @param UrlModelLocator $modelLocator
     * @param DefaultUrlHelper $parent The URL helper to proxy
     */
    public function __construct(DefaultUrlHelper $wrappedHelper = null)
    {
        if ($wrappedHelper) {
            // copy dependencies
            $this->routeMatch = $wrappedHelper->routeMatch;
            $this->router = $wrappedHelper->router;
            $this->view = $wrappedHelper->view;
        }
    }

    /**
     * @return BaseUrl
     */
    public function getBaseUrl()
    {
        if ($this->baseUrl === null) {
            $this->setBaseUrl($this->view->baseUrl());
        }

        return $this->baseUrl;
    }

    /**
     * @param BaseUrl $baseUrl
     * @return self
     */
    public function setBaseUrl($baseUrl = null)
    {
        $this->baseUrl = ($baseUrl instanceof BaseUrl)? $baseUrl : new BaseUrl($baseUrl);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\View\Helper\Url::__invoke()
     */
    public function __invoke($name = null, $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        if ($name === null) {
            return $this->getBaseUrl()->getUrl();
        }

        if ((func_num_args() == 3) && is_bool($options)) {
            // to meet this check for num args in parent method imeplementation
            $reuseMatchedParams = $options;
            $options = array();
        }

        $baseUrl = clone $this->getBaseUrl();
        $optionsContainer = new GracefulArrayAccess($options);
        $pathOnly = (bool)$optionsContainer->get('only_return_path', false);
        $options['only_return_path'] = true;
        $options['uri'] = $baseUrl->getBaseUrl();

        $url = parent::__invoke($name, $params, $options, $reuseMatchedParams);

        if ($pathOnly) {
            return $url;
        }

        return $baseUrl->getUrl($url, $options);
    }
}
