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

namespace rampage\core;

use Zend\Http\Request as HttpRequest;
use Zend\Http\PhpEnvironment\Request as PhpHttpRequest;
use Zend\Uri\Http as HttpUri;


/**
 * Base URL wrapper
 */
class BaseUrl
{
    /**
     * @var HttpRequest
     */
    private $request = null;

    /**
     * @var string
     */
    protected $rewriteBasePath = null;

    /**
     * @var bool
     */
    protected $secureByDefault = null;

    /**
     * @var HttpUri
     */
    protected $baseUrl = null;

    /**
     * @var HttpUri
     */
    protected $secureBaseUrl;

    /**
     * @param string $type
     */
    public function __construct($baseUrl = null, $secureBaseUrl = null)
    {
        if ($baseUrl !== null) {
            $this->setBaseUrl($baseUrl, false);
        }

        if ($secureBaseUrl !== null) {
            $this->setBaseUrl($secureBaseUrl, true);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getUrl();
    }

    /**
     * @param string $path
     * @return string
     */
    protected function rewriteBasePath($path)
    {
        if (!$this->rewriteBasePath) {
            return $path;
        }

        if (substr($this->rewriteBasePath, 0, 6) == 'regex:') {
            $pattern = substr($this->rewriteBasePath, 6);
            return @preg_replace($pattern, '', $path);
        }

        if (strpos($path, $this->rewriteBasePath) === 0) {
            $path = substr($path, strlen($this->rewriteBasePath));
        }

        return $path;
    }

    /**
     * @return HttpUri
     */
    protected function buildFromRequestUri($path = null, $secure = false)
    {
        $uri = new HttpUri();
        $requestUri = $this->getRequest()->getUri();
        $scheme = ($secure)? 'https' : 'http';
        $defaultPort = ($secure)? 443 : 80;

        if ($path === null) {
            $path = $this->getRequest()->getBaseUrl();
        }

        $uri->setHost($requestUri->getHost())
            ->setScheme($scheme);

        if (($requestUri->getScheme() == $scheme) && ($requestUri->getPort() != $defaultPort)) {
            $uri->setPort($requestUri->getPort());
        }

        $uri->setPath((string)$path);

        return $uri;
    }

    /**
     * @param string $path
     * @return self
     */
    public function setRewriteBasePath($path)
    {
        if ($path && (substr($path, 0, 1) != '/') && (substr($path, 0, 6) != 'regex:')) {
            $path = '/' . $path;
        }

        $this->rewriteBasePath = $path;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSecureByDefault()
    {
        if ($this->secureByDefault === null) {
            $this->secureByDefault = ($this->getRequest()->getUri()->getScheme() == 'https');
        }

        return $this->secureByDefault;
    }

    /**
     * @param boolean $flag
     * @return self
     */
    public function setSecureByDefault($flag = null)
    {
        $this->secureByDefault = ($flag === null)? null : (bool)$flag;
        return $this;
    }

    /**
     * @param string $baseUrl
     * @param bool $secure
     * @return self
     */
    public function setBaseUrl($baseUrl = null, $secure = false)
    {
        if (!$baseUrl instanceof HttpUri) {
            if (($baseUrl !== null) && preg_match('~^https?://', $baseUrl)) {
                $baseUrl = new HttpUri((string)$baseUrl);
            } else {
                $baseUrl = $this->buildFromRequestUri($baseUrl, $secure);
            }
        }

        if ($secure) {
            $this->secureBaseUrl = $baseUrl;
        } else {
            $this->baseUrl = $baseUrl;
        }

        return $this;
    }

    /**
     * @return HttpRequest
     */
    public function getRequest()
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
     * @return HttpUri
     */
    public function getSecureBaseUrl()
    {
        if (!$this->secureBaseUrl) {
            $uri = null;

            if ($this->baseUrl) {
                $uri = clone $this->baseUrl;
                $uri->setScheme('https');
            }

            $this->setBaseUrl($uri, true);
        }

        return $this->secureBaseUrl;
    }

    /**
     * @return HttpUri
     */
    public function getUnsecureBaseUrl()
    {
        if (!$this->baseUrl) {
            $this->setBaseUrl();
        }

        return $this->baseUrl;
    }

    /**
     * @param bool $secure
     * @return HttpUri
     */
    public function getBaseUrl($secure = null)
    {
        if ($secure || (($secure === null) && $this->isSecureByDefault())) {
            return $this->getSecureBaseUrl();
        }

        return $this->getUnsecureBaseUrl();
    }

    /**
     * @param string $path
     * @param array|ArrayAccess $options
     * @return HttpUri
     */
    public function getUrl($path = null, $params = null)
    {
        $params = new GracefulArrayAccess($params?: array());
        $secure = (bool)$params->get('secure', $this->isSecureByDefault());
        $extractBasePath = (bool)$params->get('extractBasePath', true);
        $uri = $this->getBaseUrl($secure);

        if ($path === null) {
            return $uri;
        }

        $uri = clone $uri;
        $base = $uri->getPath();

        if ($extractBasePath && $this->rewriteBasePath) {
            $path = $this->rewriteBasePath($path);
        } else if ($extractBasePath && !in_array($base, array('', '/'))) {
            if (substr($path, 0, 1) != '/') {
                $path = '/' . $path;
            }

            if (strpos($path, $base) === 0) {
                // Remove base path
                $path = substr($path, strlen($base));
            }
        }

        $url = rtrim($base, '/') . '/' . ltrim($path, '/');
        $uri->setPath($url);

        return $uri;
    }
}
