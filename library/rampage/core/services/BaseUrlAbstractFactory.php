<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\services;

use rampage\core\ArrayConfig;
use rampage\core\BaseUrl;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\AbstractFactoryInterface;

use Zend\Uri\Http as HttpUri;
use Zend\Http\Request as HttpRequest;


class BaseUrlAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Virtual baseurl types - do not append the type to the default base url
     *
     * @var string[]
     */
    protected $virtualTypes = [
        'resources'
    ];

    /**
     * @var string[]
     */
    protected $canonicalNameReplacements = [
        ' ' => '',
        '_' => '',
        '-' => '',
        '/' => '.',
        '\\' => '.',
    ];

    /**
     * @param string $name
     * @return string
     */
    protected function canonicalizeName($name)
    {
        return strtolower(strtr($name, $this->canonicalNameReplacements));
    }

    /**
     * @param string $type
     * @return self
     */
    public function addVirtualType($type)
    {
        $this->virtualTypes[] = $type;
        return $this;
    }

    /**
     * @param string
     * @return string|null
     */
    public function getConfigKey($name)
    {
        @list(, $key) = explode('.', $name, 2);
        return $key;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $cname = $this->canonicalizeName($requestedName);
        list($group) = explode('.', $cname, 2);
        return ($group == 'baseurl');
    }

    /**
     * @param HttpUri $uri
     * @param string $path
     * @return string
     */
    protected function appendPath(HttpUri $uri, $path)
    {
        if ($path) {
            return;
        }

        $current = $uri->getPath();
        $path = rtrim($current) . '/' . $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $cname = $this->canonicalizeName($requestedName);
        $key = $this->getConfigKey($cname);
        $config = $serviceLocator->get('Config');
        $config = new ArrayConfig(isset($config['urls'])? $config['urls'] : []);

        $baseUrl = $config->get('base_url');
        $secureUrl = $config->get('secure_base_url');
        $rewritePath = $config->get('rewrite_base_path');
        $appendPath = false;
        $appendSecurePath = false;

        if ($key) {
            $section = $config->getSection($key);

            $baseUrl = $section->get('base_url', $baseUrl);
            $secureUrl = $section->get('secure_base_url', $secureUrl);
            $rewritePath = $section->get('rewrite_base_path', $rewritePath);

            if (!in_array($key, $this->virtualTypes)) {
                if (!isset($section['base_url'])) {
                    $appendPath = $key;
                }

                if ($secureUrl && !isset($section['secure_base_url'])) {
                    $appendSecurePath = $key;
                }
            }
        }

        $baseUrl = new BaseUrl($baseUrl, $secureUrl);
        $baseUrl->setRewriteBasePath($rewritePath);

        if ($serviceLocator->has('request')) {
            $request = $serviceLocator->get('request');

            if ($request instanceof HttpRequest) {
                $baseUrl->setRequest($request);
            }
        }

        $this->appendPath($baseUrl->getUnsecureBaseUrl(), $appendPath);
        $this->appendPath($baseUrl->getSecureBaseUrl(), $appendSecurePath);

        return $baseUrl;
    }
}
