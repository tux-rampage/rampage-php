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
 * @package   rampage.test
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\test\di;

use Zend\Di\Di as DependencyInjector;
use ReflectionClass;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_Generator as MockGenerator;

/**
 * Dependency injector
 */
class Di extends DependencyInjector
{
    /**
     * Mock definitions
     *
     * @var array
     */
    private $mockDefinitions = array();

    /**
     * Construct
     */
    public function __construct(Config $config = null)
    {
        parent::__construct(null, null, $config);
    }

    /**
     * Set di config
     *
     * @param \rampage\test\di\Config $config
     */
    public function setConfig($config)
    {
        if (!$config instanceof Config) {
            $config = new Config($config);
        }

        $config->configure($this);
        return $this;
    }

	/**
     * Add a mock definition
     *
     * @param string $class
     * @param array $methods
     */
    public function addMockDefinition($class, array $methods)
    {
        if (!isset($this->mockDefinitions[$class])) {
            $this->mockDefinitions[$class] = $methods;
        } else {
            $this->mockDefinitions[$class] = array_merge($this->mockDefinitions[$class], $methods);
        }

        return $this;
    }

    /**
     * Add multiple mock definitions
     *
     * @param array $definition
     * @throws InvalidArgumentException
     * @return \rampage\test\di\Di
     */
    public function addMockDefinitions(array $definition)
    {
        foreach ($definition as $class => $methods) {
            if (!is_array($methods)) {
                throw new InvalidArgumentException('Mock definition for "' . $class . '" must be an array!');
            }

            $this->addMockDefinition($class, $methods);
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Di\Di::newInstance()
     */
    public function newInstance($name, array $params = array(), $isShared = true)
    {
        $reflection = new ReflectionClass($name);
        if (!isset($this->mockDefinitions[$name]) && !$reflection->isInterface() && !$reflection->isAbstract()) {
            return parent::newInstance($name, $params, $isShared);
        }

        // Mockery
        $methods = (isset($this->mockDefinitions[$name]))? $this->mockDefinitions[$name] : array();
        $callConstruct = false;

        if (array_key_exists('__construct', $methods)) {
            $callConstruct = (bool)$methods['__construct'];
            unset($methods['__construct']);
        }

        $methodNames = array_keys($methods);

        /* @var $method \ReflectionMethod */
        if ($reflection->isInterface() || $reflection->isAbstract()) {
            foreach ($reflection->getMethods() as $method) {
                $methodName = $method->getName();

                // Make fluent interfaces
                if (!isset($methods[$methodName]) && substr($methodName, 0, 3) == 'set') {
                    $methods[$methodName] = array(null, 'self');
                }
            }

            $mock = MockGenerator::getMockForAbstractClass($name, array(), '', $callConstruct, true, true, $methodNames);
        } else {
            $mock = MockGenerator::getMock($name, $methodNames, array(), '', $callConstruct);
        }


        foreach ($methods as $method => $spec) {
            $expectArgs = null;

            if (is_callable($spec)) {
                $expects = new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount();
                $return = new \PHPUnit_Framework_MockObject_Stub_ReturnCallback($spec);
            } else if (is_array($spec)) {
                @list($value, $type, $expectType, $expectArgs) = $spec;
                switch ($type) {
                    case 'self':
                        $return = new \PHPUnit_Framework_MockObject_Stub_ReturnSelf();
                        break;

                    case 'callback':
                        $return = new \PHPUnit_Framework_MockObject_Stub_ReturnCallback($value);
                        break;

                    case 'value':
                    default:
                        $return = new \PHPUnit_Framework_MockObject_Stub_Return($value);
                        break;
                }

                switch ($expectType) {
                    case 'atleastonce':
                        $expects = new \PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce();
                        break;

                    case 'once':
                        $expects = new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(1);
                        break;

                    case 'never':
                        $expects = new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(0);
                        break;

                    default:
                        if (is_int($expectType)) {
                            $expects = new \PHPUnit_Framework_MockObject_Matcher_InvokedCount($expectType);
                        } else {
                            $expects = new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount();
                        }

                        break;
                }
            } else {
                $expects = new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount();
                $return = new \PHPUnit_Framework_MockObject_Stub_Return($spec);
            }

            $constraint = $mock->expects($expects)->method($method);
            if (is_array($expectArgs)) {
                call_user_func_array(array($constraint, 'with'), $expectArgs);
            }

            $constraint->will($return);
        }

        return $mock;
    }
}