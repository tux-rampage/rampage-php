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

use rampage\core\BaseUrl;
use rampage\core\GracefulArrayAccess;

use Zend\Mvc\Controller\Plugin\Url as ZendUrlPlugin;

/**
 * Url plugin that'll make use of the base url
 */
class UrlPlugin extends ZendUrlPlugin
{
    /**
     * @var BaseUrl
     */
    private $baseUrl = null;

    /**
     * @param BaseUrl|\Zend\Uri\Http|string $baseUrl
     */
    public function __construct($baseUrl = null)
    {
        $this->baseUrl = ($baseUrl instanceof BaseUrl)? $baseUrl : new BaseUrl($baseUrl);
    }

    /**
     * @see \Zend\Mvc\Controller\Plugin\Url::fromRoute()
     * @return \Zend\Uri\Http
     */
    public function fromRoute($name = null, array $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        if ((func_num_args() == 3) && is_bool($options)) {
            // to meet this check for num args in parent method imeplementation
            $reuseMatchedParams = $options;
            $options = array();
        }

        if (!is_array($options) && !($options instanceof \ArrayAccess)) {
            $options = array();
        }

        $baseUrl = clone $this->baseUrl;
        $pathOnly = (new GracefulArrayAccess($options))->get('only_return_path');
        $options['uri'] = $baseUrl->getUrl(null, $options);
        $options['only_return_path'] = true;

        $url = parent::fromRoute($name, $params, $options, $reuseMatchedParams);

        if ($pathOnly) {
            return $url;
        }

        return $baseUrl->getUrl($url, $options);
    }
}
