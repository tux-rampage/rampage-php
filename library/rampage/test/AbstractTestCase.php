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

namespace rampage\test;

use PHPUnit_Framework_TestCase as TestCase;
use ReflectionObject;
use ReflectionClass;
use SplFileInfo;
use InvalidArgumentException;

// DI
use rampage\test\di\Di;
use rampage\test\di\Config as DiConfig;

/**
 * Abstract test case
 */
class AbstractTestCase extends TestCase
{
    /**
     * Dependency injector
     *
     * @var \rampage\test\di\Di
     */
    private $di = null;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->di = null;
    }

    /**
     * Default DI mock config
     *
     * @return array|\rampage\test\di\Config
     */
    protected function getDefaultDiConfig()
    {
        $file = $this->getResourcePath('di.default.php');
        if (is_readable($file) && is_file($file)) {
            return include $file;
        }

        return null;
    }

    /**
     * Check if DI container is initialized
     *
     * @return boolean
     */
    protected function hasInitializedDiContainer()
    {
        return ($this->di !== null);
    }

    /**
     * Dependency injector
     *
     * @return \rampage\test\di\Di
     */
    protected function di()
    {
        if (!$this->di) {
            $config = $this->getDefaultDiConfig();
            if (!$config instanceof DiConfig) {
                $config = new DiConfig($config);
            }

            $this->di = new Di($config);
        }

        return $this->di;
    }

	/**
     * Returns the resource path
     *
     * @param string $file
     */
    protected function getResourcePath($file = null)
    {
        $reflection = new ReflectionObject($this);
        $info = new SplFileInfo($reflection->getFileName());
        $cwd = (defined('RAMPAGE_TEST_DIR'))? RAMPAGE_TEST_DIR : getcwd();

        if (strpos($info->getPathname(), $cwd . '/') === 0) {
            $segment = trim(substr($info->getPath(), strlen($cwd)), '/');
            $segment = strtr($segment, array('\\' => '.', '/' => '.'));
            $path = $cwd . '/_resources/' . $segment . '.' . $info->getBasename('.php');
        } else {
            $path = $info->getPath() . '/_resources/' . $info->getBasename('.php');
        }

        if ($file) {
            $path .= '/' . ltrim($file, '/');
        }

        return $path;
    }

    /**
     * Recursive array ksort
     * @param array $array
     */
    protected function recursiveArrayKeySort(&$array)
    {
        if (!is_array($array)) {
            return;
        }

        ksort($array);
        array_walk($array, array($this, 'recursiveArrayKeySort'));
    }

    /**
     * Mock the given interface
     *
     * @param string $interface
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockInterface($interface, array $methods)
    {
        if (!interface_exists($interface)) {
            throw new InvalidArgumentException('No such interface: ' . $interface);
        }

        $reflection = new ReflectionClass($interface);

        /* @var $method \ReflectionMethod */
        foreach ($reflection->getMethods() as $method) {
            $methodName = $method->getName();
            if (isset($methods[$methodName]) || (substr($methodName, 0, 3) != 'set')) {
                continue;
            }

            $methods[$methodName] = array(null, 'self');
        }

        $mock = $this->getMockForAbstractClass($interface);
        foreach ($methods as $method => $spec) {
            if (is_callable($spec)) {
                $return = $this->returnCallback($spec);
            } else if (is_array($spec)) {
                @list($value, $type) = $spec;
                switch ($type) {
                    case 'self':
                        $return = $this->returnSelf();
                        break;

                    case 'value':
                    default:
                        $return = $this->returnValue($value);
                        break;
                }
            } else {
                $return = $this->returnValue($spec);
            }

            $mock->expects($this->any())
                 ->method($method)
                 ->will($return);
        }

        return $mock;
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::verifyMockObjects()
     */
    protected function verifyMockObjects()
    {
        parent::verifyMockObjects();

        // verify asserts in DI container
        if ($this->hasInitializedDiContainer()) {
            // Need to add assertion count via reflection since this property is private for some idiotic reason
            $reflection = new ReflectionClass('PHPUnit_Framework_TestCase');
            $property = $reflection->getProperty('numAssertions');

            $property->setAccessible(true);
            $assertCount = $property->getValue($this);

            $this->di()->verifyAssertsInMockObjects($assertCount);
            $property->setValue($this, $assertCount);

            $property = null;
            $reflection = null;
        }
    }
}
