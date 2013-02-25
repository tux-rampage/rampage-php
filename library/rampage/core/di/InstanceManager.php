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

namespace rampage\core\di;

use Zend\Di\InstanceManager as DefaultInstanceManager;
use Zend\Di\Exception;
use rampage\core\ObjectManagerInterface;

/**
 * Instance manager
 */
class InstanceManager extends DefaultInstanceManager
{
    /**
     * The template to use for housing configuration information
     *
     * Do NOT share by default, sharing instances is the domain of the service manager!
     *
     * @var array
     */
    protected $configurationTemplate = array(
        /**
         * alias|class => alias|class
         * interface|abstract => alias|class|object
         * name => value
         */
        'parameters' => array(),
        /**
         * injection type => array of ordered method params
         */
        'injections' => array(),
        'shared' => false
    );

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager = null)
    {
        if ($objectManager) {
            $this->setObjectManager($objectManager);
        }
    }

    /**
     * Object Manager
     *
     * @var \rampage\core\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Set the current object manager
     *
     * @param ObjectManagerInterface $manager
     */
    public function setObjectManager(ObjectManagerInterface $manager)
    {
        $this->objectManager = $manager;
        return $this;
    }

    /**
     * Check if service exists
     *
     * @param string $name
     */
    public function hasService($name)
    {
        // consult object manager if there is an instance
        if (!$this->objectManager) {
            return false;
        }

        return $this->objectManager->has($name);
    }

    /**
     * Returns a specific service instance
     *
     * @param string $name
     * @return object
     */
    public function getService($name, array $params = array())
    {
        if (!$this->objectManager) {
            throw new Exception\UndefinedReferenceException('Cannot get service without an object manager instance!');
        }

        return $this->objectManager->get($name, $params);
    }
}