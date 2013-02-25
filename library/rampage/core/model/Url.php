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

namespace rampage\core\model;

use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Uri\Http as HttpUri;

/**
 * URL Model
 */
class Url
{
    /**
     * HTTP request
     *
     * @var HttpRequest
     */
    private $request = null;

    /**
     * Base Url
     *
     * @var array[\Zend\Http\Uri]
     */
    protected $baseUrl = array();

    /**
     * Http request
     *
     * @param Config $config
     * @param HttpRequest $request
     * @return \rampage\core\model\Url
     */
    public function __construct(Config $config, HttpRequest $request = null)
    {
        if (!$request) {
            $request = new HttpRequest();
        }

        $this->request = $request;
        return $this;
    }

    /**
     * Set application config
     *
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $config->configureUrlModel($this);
    }

    /**
     * Set the base url
     *
     * @param string $baseUrl
     * @return \rampage\core\model\Url
     */
    public function setBaseUrl($baseUrl, $secure = false)
    {
        $type = ($secure)? 'secure' : 'unsecure';
        if (preg_match('~^https?://~', $baseUrl)) {
            $this->baseUrl[$type] = new HttpUri($baseUrl);
            return $this;
        } else if ($baseUrl instanceof HttpUri) {
            $this->baseUrl[$type] = $baseUrl;
            return $this;
        }

        $uri = new HttpUri();
        $requestUri = $this->request->getUri();
        $scheme = ($secure)? 'https' : 'http';
        $defaultPort = ($secure)? 443 : 80;

        $uri->setHost($requestUri->getHost())
            ->setScheme($scheme);

        if (($requestUri->getScheme() == $scheme) && ($requestUri->getPort() != $defaultPort)) {
            $uri->setPort($requestUri->getPort());
        }

        $uri->setPath((string)$baseUrl);
        $this->baseUrl[$type] = $uri;

        return $this;
    }

    /**
     * Base url
     *
     * @param bool $secure
     * @return \Zend\Uri\Http
     */
    protected function getBaseUrl($secure = false)
    {
        $type = ($secure)? 'secure' : 'unsecure';
        if (isset($this->baseUrl[$type])) {
            return $this->baseUrl[$type];
        }

        if ($secure && isset($this->baseUrl['unsecure'])) {
            $default = clone $this->baseUrl['unsecure'];
        } else {
            $default = $this->request->getBaseUrl();
        }

        $this->setBaseUrl($default, $secure);
        return $this->baseUrl[$type];
    }

    /**
     * Returns the URL
     *
     * @return string
     */
    public function getUrl($path, $params = null)
    {
        $uri = clone $this->getBaseUrl();
        $base = $uri->getPath();
        $url = $base . '/' . ltrim($path);

        $uri->setPath($url);
        if (isset($params) && $params[''])

        return $uri;
    }
}