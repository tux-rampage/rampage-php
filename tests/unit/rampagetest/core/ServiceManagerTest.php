<?php
/**
 * This is part of @application_name@
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
 * @package   @package_name@
 * @author    Axel Helmert
 * @copyright Copyright (c) 2012 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampagetest\core;

use rampage\core\ServiceManager;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Service manager test
 */
class ServiceManagerTest extends TestCase
{
    /**
     * Service manager test
     *
     * @var \rampage\core\ServiceManager
     */
    private $_instance = null;

	/**
	 * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->_instance = new ServiceManager();
    }

	/**
	 * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->_instance = null;
    }

    /**
     * Data provider for canonical names
     *
     * @return string
     */
    public function nameCanonicalizationDataProvider()
    {
        return array(
            array('foo\bar\Baz', 'foo.bar.baz'),
            array('hello.WorldExample', 'hello.worldexample'),
            array('some_underscore_service', 'someunderscoreservice'),
        );
    }

    /**
     * Test name canonicalization
     *
     * @dataProvider nameCanonicalizationDataProvider
     * @param string $name
     * @param string $expected
     */
    public function testNameCanonicalization($name, $expected)
    {
        $this->_instance->setInvokableClass($name, 'stdClass');
        $this->assertTrue($this->_instance->has($expected, false, false));
    }

    /**
     * Test shared service works
     */
    public function testSharedServiceWorksWithShareByDefaultFalse()
    {
        $this->_instance->setAllowOverride(true);
        $this->_instance->setShareByDefault(false);
        $this->_instance->setInvokableClass('sharedtest', 'stdClass', true);
        $a = $this->_instance->get('sharedtest');

        $this->assertSame($a, $this->_instance->get('sharedtest'), 'Registered service is expected to be shared');
    }
}