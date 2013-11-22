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

namespace rampage\core\url;

use rampage\core\UserConfig;
use Zend\Http\Request as HttpRequest;
use Zend\Http\PhpEnvironment\Request as PhpHttpRequest;
use Zend\Uri\Http as HttpUri;

/**
 * URL Model
 */
class BaseUrl implements UrlModelInterface
{
    /**
     * HTTP request
     *
     * @var HttpRequest
     */
    private $request = null;

    /**
     * Type
     *
     * @var string
     */
    protected $type = null;

    /**
     * Base Url
     *
     * @var array[\Zend\Http\Uri]
     */
    private $baseUrl = array();

    /**
     * Returns the base url type
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set application config
     *
     * @param Config $config
     */
    public function setConfig(UserConfig $config)
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
        $requestUri = $this->getRequest()->getUri();
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
     * HTTP request
     *
     * @return \Zend\Http\PhpEnvironment\Request
     */
    protected function getRequest()
    {
        if (!$this->request) {
            $this->setRequest(new PhpHttpRequest());
        }

        return $this->request;
    }

    /**
     * @param HttpRequest $request
     * @return self
     */
    public function setRequest(HttpRequest $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Default base url
     *
     * @return string
     */
    protected function getDefaultBaseUrl()
    {
        $baseUrl = $this->getRequest()->getBaseUrl();

        if ($type = $this->getType()) {
            $baseUrl = rtrim($baseUrl, '/') . '/' . trim($type, '/');
        }

        return $baseUrl;
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
            $default = $this->getDefaultBaseUrl();
        }

        $this->setBaseUrl($default, $secure);
        return $this->baseUrl[$type];
    }

    /**
     * Returns the URL
     *
     * @return \Zend\Uri\Http
     */
    public function getUrl($path = null, $params = null)
    {
        $secure = (isset($params['secure']))? (bool)$params['secure'] : ($this->getRequest()->getUri()->getScheme() == 'https');
        $uri = clone $this->getBaseUrl($secure);

        if ($path === null) {
            return $uri;
        }

        $base = $uri->getPath();
        $url = $base . '/' . ltrim($path, '/');

        $uri->setPath($url);
        return $uri;
    }
}
