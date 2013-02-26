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

use Zend\Di\Di as DependencyInjector;
use Zend\Di\DefinitionList;
use Zend\Di\Config;
use Zend\Di\Exception;
use Closure;

/**
 * Dependency injector
 *
 * @property \rampage\core\di\InstanceManager $instanceManager
 */
class Di extends DependencyInjector
{
    /**
     * Enforce custom instance manager
     *
     * @see \Zend\Di\Di::__construct()
     */
    public function __construct(DefinitionList $definitions = null, InstanceManager $instanceManager = null, Config $config = null)
    {
        parent::__construct($definitions, $instanceManager, $config);
    }

    /**
     * Instance manager
     *
     * @see \Zend\Di\Di::instanceManager()
     * @return \rampage\core\di\InstanceManager
     */
    public function instanceManager()
    {
        return $this->instanceManager;
    }

    /**
     * Returns the instance manager
     *
     * @return \rampage\core\di\InstanceManager
     */
    public function getInstanceManager()
    {
        return $this->instanceManager;
    }

	/**
     * Resolve type preferences
     *
     * @param string $type
     * @param string $fqParamPos
     * @param array $computedParams
     * @return \rempage\core\di\Di
     */
    protected function resolveTypePreference($type, $fqParamPos, &$computedParams)
    {
        if (!$type || !$this->instanceManager->hasTypePreferences($type)) {
            return false;
        }

        $pInstances = $this->instanceManager->getTypePreferences($type);
        foreach ($pInstances as $pInstance) {
            if (is_object($pInstance)) {
                $computedParams['value'][$fqParamPos] = $pInstance;
                return true;
            }

            $pInstanceClass = ($this->instanceManager->hasAlias($pInstance)) ?
            $this->instanceManager->getClassFromAlias($pInstance) : $pInstance;

            if ($pInstanceClass === $type
              || self::isSubclassOf($pInstanceClass, $type)
              || ($this->instanceManager->hasService($pInstanceClass))) {
                $computedParams['required'][$fqParamPos] = array($pInstance, $pInstanceClass);
                return true;
            }
        }

        return false;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Di\Di::get()
     */
    public function get($name, array $params = array())
    {
        if ($this->instanceManager->hasService($name)) {
            return $this->instanceManager->getService($name);
        }

        return parent::get($name, $params);
    }

    /**
     * Resolve parameters referencing other services
     *
     * @param  string                                $class
     * @param  string                                $method
     * @param  array                                 $callTimeUserParams
     * @param  string                                $alias
     * @param  bool                                  $methodIsRequired
     * @param  bool                                  $isInstantiator
     * @throws Exception\MissingPropertyException
     * @throws Exception\CircularDependencyException
     * @return array
     */
    protected function resolveMethodParameters($class, $method, array $callTimeUserParams, $alias, $methodIsRequired, $isInstantiator = false)
    {
        // parameters for this method, in proper order, to be returned
        $resolvedParams = array();

        // parameter requirements from the definition
        $injectionMethodParameters = $this->definitions->getMethodParameters($class, $method);

        // computed parameters array
        $computedParams = array(
            'value'    => array(),
            'required' => array(),
            'optional' => array()
        );

        // retrieve instance configurations for all contexts
        $iConfig = array();
        $aliases = $this->instanceManager->getAliases();

        // for the alias in the dependency tree
        if ($alias && $this->instanceManager->hasConfig($alias)) {
            $iConfig['thisAlias'] = $this->instanceManager->getConfig($alias);
        }

        // for the current class in the dependency tree
        if ($this->instanceManager->hasConfig($class)) {
            $iConfig['thisClass'] = $this->instanceManager->getConfig($class);
        }

        // for the parent class, provided we are deeper than one node
        if (isset($this->instanceContext[0])) {
            list($requestedClass, $requestedAlias) = ($this->instanceContext[0][0] == 'NEW')
            ? array($this->instanceContext[0][1], $this->instanceContext[0][2])
            : array($this->instanceContext[1][1], $this->instanceContext[1][2]);
        } else {
            $requestedClass = $requestedAlias = null;
        }

        if ($requestedClass != $class && $this->instanceManager->hasConfig($requestedClass)) {
            $iConfig['requestedClass'] = $this->instanceManager->getConfig($requestedClass);
            if ($requestedAlias) {
                $iConfig['requestedAlias'] = $this->instanceManager->getConfig($requestedAlias);
            }
        }

        // This is a 2 pass system for resolving parameters
        // first pass will find the sources, the second pass will order them and resolve lookups if they exist
        // MOST methods will only have a single parameters to resolve, so this should be fast

        foreach ($injectionMethodParameters as $fqParamPos => $info) {
            list($name, $type, $isRequired) = $info;

            $fqParamName = substr_replace($fqParamPos, ':' . $info[0], strrpos($fqParamPos, ':'));

            // PRIORITY 1 - consult user provided parameters
            if (isset($callTimeUserParams[$fqParamPos]) || isset($callTimeUserParams[$name])) {
                if (isset($callTimeUserParams[$fqParamPos])) {
                    $callTimeCurValue =& $callTimeUserParams[$fqParamPos];
                } elseif (isset($callTimeUserParams[$fqParamName])) {
                    $callTimeCurValue =& $callTimeUserParams[$fqParamName];
                } else {
                    $callTimeCurValue =& $callTimeUserParams[$name];
                }

                if ($type !== false && is_string($callTimeCurValue)) {
                    if ($this->instanceManager->hasAlias($callTimeCurValue)) {
                        // was an alias provided?
                        $computedParams['required'][$fqParamPos] = array(
                            $callTimeUserParams[$name],
                            $this->instanceManager->getClassFromAlias($callTimeCurValue)
                        );
                    } elseif ($this->definitions->hasClass($callTimeUserParams[$name]) || $this->instanceManager->hasService($callTimeCurValue)) {
                        // was a known class provided?
                        $computedParams['required'][$fqParamPos] = array(
                            $callTimeCurValue,
                            $callTimeCurValue
                        );
                    } else {
                        // must be a value
                        $computedParams['value'][$fqParamPos] = $callTimeCurValue;
                    }
                } else {
                    // int, float, null, object, etc
                    $computedParams['value'][$fqParamPos] = $callTimeCurValue;
                }

                unset($callTimeCurValue);
                continue;
            }

            // PRIORITY 2 -specific instance configuration (thisAlias) - this alias
            // PRIORITY 3 -THEN specific instance configuration (thisClass) - this class
            // PRIORITY 4 -THEN specific instance configuration (requestedAlias) - requested alias
            // PRIORITY 5 -THEN specific instance configuration (requestedClass) - requested class

            foreach (array('thisAlias', 'thisClass', 'requestedAlias', 'requestedClass') as $thisIndex) {
                // check the provided parameters config
                if (isset($iConfig[$thisIndex]['parameters'][$fqParamPos])
                || isset($iConfig[$thisIndex]['parameters'][$fqParamName])
                || isset($iConfig[$thisIndex]['parameters'][$name])) {

                    if (isset($iConfig[$thisIndex]['parameters'][$fqParamPos])) {
                        $iConfigCurValue =& $iConfig[$thisIndex]['parameters'][$fqParamPos];
                    } elseif (isset($iConfig[$thisIndex]['parameters'][$fqParamName])) {
                        $iConfigCurValue =& $iConfig[$thisIndex]['parameters'][$fqParamName];
                    } else {
                        $iConfigCurValue =& $iConfig[$thisIndex]['parameters'][$name];
                    }

                    if ($type === false && is_string($iConfigCurValue)) {
                        $computedParams['value'][$fqParamPos] = $iConfigCurValue;
                    } elseif (is_string($iConfigCurValue)
                      && isset($aliases[$iConfigCurValue])) {
                        $computedParams['required'][$fqParamPos] = array(
                            $iConfig[$thisIndex]['parameters'][$name],
                            $this->instanceManager->getClassFromAlias($iConfigCurValue)
                        );
                    } elseif (is_string($iConfigCurValue)
                      && ($this->definitions->hasClass($iConfigCurValue)
                      || $this->instanceManager->hasService($iConfigCurValue))) {
                        $computedParams['required'][$fqParamPos] = array(
                            $iConfigCurValue,
                            $iConfigCurValue
                        );
                    } elseif (is_object($iConfigCurValue)
                      && $iConfigCurValue instanceof Closure
                      && $type !== 'Closure') {
                        /* @var $iConfigCurValue Closure */
                        $computedParams['value'][$fqParamPos] = $iConfigCurValue();
                    } else {
                        $computedParams['value'][$fqParamPos] = $iConfigCurValue;
                    }
                    unset($iConfigCurValue);
                    continue 2;
                }
            }

            // PRIORITY 6 - globally preferred implementations

            // next consult alias level preferred instances
            if ($this->resolveTypePreference($alias, $fqParamPos, $computedParams)) {
                continue;
            }

            // next consult class level preferred instances
            if ($this->resolveTypePreference($type, $fqParamPos, $computedParams)) {
                continue;
            }

            if (!$isRequired) {
                $computedParams['optional'][$fqParamPos] = true;
            }

            if ($type && $isRequired && $methodIsRequired) {
                $computedParams['required'][$fqParamPos] = array($type, $type);
            }

        }

        $index = 0;
        foreach ($injectionMethodParameters as $fqParamPos => $value) {
            $name = $value[0];

            if (isset($computedParams['value'][$fqParamPos])) {
                // if there is a value supplied, use it
                $resolvedParams[$index] = $computedParams['value'][$fqParamPos];
            } elseif (isset($computedParams['required'][$fqParamPos])) {
                // detect circular dependencies! (they can only happen in instantiators)
                if ($isInstantiator && in_array($computedParams['required'][$fqParamPos][1], $this->currentDependencies)) {
                    throw new Exception\CircularDependencyException(
                        "Circular dependency detected: $class depends on {$value[1]} and viceversa"
                    );
                }

                array_push($this->currentDependencies, $class);
                $resolvedParams[$index] = $this->get($computedParams['required'][$fqParamPos][0], $callTimeUserParams);
                array_pop($this->currentDependencies);
            } elseif (!array_key_exists($fqParamPos, $computedParams['optional'])) {
                if ($methodIsRequired) {
                    // if this item was not marked as optional,
                    // plus it cannot be resolve, and no value exist, bail out
                    throw new Exception\MissingPropertyException(sprintf(
                        'Missing %s for parameter ' . $name . ' for ' . $class . '::' . $method,
                        (($value[0] === null) ? 'value' : 'instance/object')
                    ));
                } else {
                    return false;
                }
            } else {
                $resolvedParams[$index] = $value[3];
            }

            $index++;
        }

        return $resolvedParams; // return ordered list of parameters
    }
}