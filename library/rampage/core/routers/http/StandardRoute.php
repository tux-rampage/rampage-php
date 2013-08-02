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

use ArrayAccess;
use rampage\core\exception\InvalidArgumentException;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Mvc\Router\Http\RouteMatch;

/**
 * Standard router
 */
class StandardRoute implements RouteInterface
{
    /**
     * Frontname
     *
     * @var unknown
     */
    protected $frontname = null;

    /**
     * Namespace to use
     *
     * @var array
     */
    protected $namespace = null;

    /**
     * Default values
     *
     * @var string
     */
    protected $defaults = array();

    /**
     * Allowed parameters
     *
     * @var array
     */
    protected $allowedParams = array();

    /**
     * Assembled parameters
     *
     * @var array
     */
    protected $assembledParams = array();

    /**
     * Map of allowed special chars in path segments.
     *
     * http://tools.ietf.org/html/rfc3986#appendix-A
     * segement      = *pchar
     * pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"
     * unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
     * sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
     *               / "*" / "+" / "," / ";" / "="
     *
     * @var array
     */
    private static $urlencodeCorrectionMap = array(
        '%21' => "!", // sub-delims
        '%24' => "$", // sub-delims
        '%26' => "&", // sub-delims
        '%27' => "'", // sub-delims
        '%28' => "(", // sub-delims
        '%29' => ")", // sub-delims
        '%2A' => "*", // sub-delims
        '%2B' => "+", // sub-delims
        '%2C' => ",", // sub-delims
//      '%2D' => "-", // unreserved - not touched by rawurlencode
//      '%2E' => ".", // unreserved - not touched by rawurlencode
        '%3A' => ":", // pchar
        '%3B' => ";", // sub-delims
        '%3D' => "=", // sub-delims
        '%40' => "@", // pchar
//      '%5F' => "_", // unreserved - not touched by rawurlencode
        '%7E' => "~", // unreserved
    );

    /**
     * Construct
     */
    public function __construct($frontname, $namespace, $defaults = null, $allowedParams = null)
    {
        if (!$namespace) {
            throw new InvalidArgumentException('Namespace must not be empty!');
        }

        if (!$frontname) {
            throw new InvalidArgumentException('Frontname must not be empty');
        }

        $this->frontname = $frontname;
        $this->namespace = $namespace;

        if (is_array($defaults) || ($defaults instanceof ArrayAccess)) {
            $this->defaults = $defaults;
        }

        if (is_array($allowedParams)) {
            $this->allowedParams = $allowedParams;
        }
    }

    /**
     * Encode a path segment.
     *
     * @param string $value
     * @return string
     */
    private function encode($value)
    {
        $result = rawurlencode($value);
        $result = strtr($result, self::$urlencodeCorrectionMap);

        return $result;
    }

    /**
     * Decode a path segment.
     *
     * @param string $value
     * @return string
     */
    private function decode($value)
    {
        return rawurldecode($value);
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
          || !self::arrayHasKeys($options, array('frontname', 'namespace'))) {
            throw new InvalidArgumentException('Invalid router options specified.');
        }

        $defaults = (isset($options['defaults']))? $options['defaults'] : null;
        $params = (isset($options['allowed_params']))? $options['allowed_params'] : null;
        return new static($options['frontname'], $options['namespace'], $defaults, $params);
    }


    /**
     * (non-PHPdoc)
     * @see \Zend\Mvc\Router\Http\RouteInterface::getAssembledParams()
     */
    public function getAssembledParams()
    {
        return $this->assembledParams;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Mvc\Router\RouteInterface::assemble()
     */
    public function assemble(array $params = array(), array $options = array())
    {
        $params = array_merge($this->defaults, $params);
        $controller = (isset($params['controller']))? $params['controller'] : 'index';
        $action = (isset($params['action']))? $params['action'] : 'index';

        if (strpos($controller, $this->namespace . '.') == 0) {
            $controller = substr($controller, strlen($this->namespace) + 1);
        }

        $result = '/' . $this->frontname . '/' . $controller . '/' . $action;

        foreach ($this->allowedParams as $param) {
            if (!isset($params[$param]) || preg_match('~/|#|\?~', $params[$param])) {
                continue;
            }

            $this->assembledParams[] = $param;
            $result .= '/' . $this->encode($param) . '/' . $this->encode($params[$param]);
        }

        return $result;
    }

    /**
     * Extract parameters
     *
     * @param string $path
     * @param array $params
     * @return int The length of the extracted data. Should be added to total length for RouteMatch
     */
    protected function extractParameters($path, array &$params)
    {
        if (!$path) {
            return 0;
        }

        $size = 0;
        $parts = explode('/', $path);

        while (count($parts) > 1) {
            $rawKey = array_shift($parts);
            $key = $this->decode($rawKey);
            if (!in_array($key, $this->allowedParams)) {
                break;
            }

            $value = array_shift($parts);
            $params[$key] = $this->decode($value);

            $size += strlen($rawKey) + strlen($value) + 2; // +2 = two slashes: /$key/$value
        }

        return $size;
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

        $uri  = $request->getUri();
        $path = $uri->getPath();

        if ($pathOffset !== null) {
            $path = substr($path, $pathOffset);
        }

        $trimmedPath = ltrim($path, '/');
        $length = strlen($path) - strlen($trimmedPath);
        $parts = explode('/', $trimmedPath, 4);

        if (count($parts) < 1) {
            return null;
        }

        @list($frontname, $controller, $action, $pathParams) = $parts;
        if ($frontname != $this->frontname) {
            return null;
        }

        if (count($parts) > 3) {
            array_pop($parts);
        }

        $params = $this->defaults;
        $controller = ($controller)?: (isset($params['controller'])? $params['controller'] : 'index');
        $action = ($action)?: (isset($params['action'])? $params['action'] : 'index');

        $length += strlen(implode('/', $parts));
        $length += $this->extractParameters($pathParams, $params);

        $params['controller'] = $this->namespace . '.' . $controller;
        $params['action'] = $action;

        return new RouteMatch($params, $length);
    }
}