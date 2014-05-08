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

use rampage\core\di\DIContainerAware;
use rampage\core\exception\InvalidPluginException;

use Zend\Di\Di;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;

/**
 * Url repository
 */
class UrlModelLocator extends AbstractPluginManager implements DIContainerAware
{
    /**
     * @var Di
     */
    private $di = null;

    /**
     * @var array
     */
    protected $invokableClasses = array(
        'base' => 'rampage\core\url\BaseUrl',
        'media' => 'rampage\core\url\MediaUrl',
        'static' => 'rampage\core\url\StaticUrl',
    );

    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::__construct()
     */
    public function __construct(UrlConfigInterface $urlConfig, ConfigInterface $configuration = null)
    {
        $this->addInitializer(array($urlConfig, 'configureUrlModel'));
        parent::__construct($configuration);
    }

    /**
     * @see \rampage\core\di\DIContainerAware::setDIContainer()
     */
    public function setDIContainer(Di $container)
    {
        $this->di = $container;
        return $this;
    }

    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::createFromInvokable()
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        $invokable = $this->invokableClasses[$canonicalName];
        $options = (is_array($this->creationOptions))? $this->creationOptions : array();

        if (!$this->di) {
            return parent::createFromInvokable($canonicalName, $requestedName);
        }

        return $this->di->newInstance($invokable, $options, false);
    }

    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::validatePlugin()
     */
    public function validatePlugin($plugin)
    {
        if (!$plugin instanceof UrlModelInterface) {
            throw new InvalidPluginException('Invalid plugin type "%s", an instance of "rampage\core\url\UrlModel" is expected');
        }
    }

    /**
     * Returns the requested URL model
     *
     * @param string $type
     * @return rampage\core\model\Url
     */
    public function getUrlModel($type = null)
    {
        $type = ($type)? (string)$type : 'base';
        return $this->get($type);
    }
}
