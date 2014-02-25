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
use rampage\core\ServiceConfig;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Testing default service config
 */
class ServiceConfigTest extends TestCase
{
    /**
     * Service config instance
     *
     * @var \rampage\core\ServiceConfig
     */
    private $instance = null;

	/**
	 * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->instance = new ServiceConfig();
    }

	/**
	 * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->instance = null;
    }

    /**
     * Test config on default services
     */
    public function testDefaultServices()
    {
        $sm = new ServiceManager($this->instance);
        $this->assertInstanceOf('rampage\core\PathManager', $sm->get('rampage.PathManager'));
        $this->assertInstanceOf('rampage\core\PathManager', $sm->get('PathManager'));
    }
}
