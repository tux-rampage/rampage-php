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

use rampage\core\PathManager;
use rampage\test\AbstractTestCase;

/**
 * Pathmanager test
 */
class PathManagerTest extends AbstractTestCase
{
    /**
     * Pathmanager
     *
     * @var \rampage\core\PathManager
     */
    private $_manager = null;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->_manager = new PathManager();
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->_manager = null;
    }

    public function testDefaultRootDirectory()
    {
        $this->assertEquals(getcwd(), $this->_manager->get('root'));
    }

    public function testCustomRootDir()
    {
        $tempDir = sys_get_temp_dir();
        $manager = new PathManager($tempDir);

        $this->assertEquals($tempDir, $manager->get('root'));
        $this->assertNotEquals(getcwd(), $manager->get('root'));
    }

    /**
     * Test if path placeholder is replaced
     */
    public function testReplacementWorks()
    {
        $this->_manager->set('foo', '{{root_dir}}/foo');
        $this->assertEquals(getcwd() . '/foo', $this->_manager->get('foo'));
    }

    /**
     * Test default directories init
     */
    public function testDefaultDirectories()
    {
        $this->assertEquals(getcwd().'/application', $this->_manager->get('app'));
        $this->assertEquals(getcwd().'/public', $this->_manager->get('public'));
        $this->assertEquals(getcwd().'/var', $this->_manager->get('var'));
        $this->assertEquals(getcwd().'/var/cache', $this->_manager->get('cache'));
        $this->assertEquals(getcwd().'/public/media', $this->_manager->get('media'));
    }

    /**
     * Test array input
     */
    public function testArrayInput()
    {
        $manager = new PathManager(array(
            'root' => sys_get_temp_dir(),
            'app' => '/custom/app/dir',
            'foo' => '{{root_dir}}/foo'
        ));

        $this->assertEquals(sys_get_temp_dir(), $manager->get('root'));
        $this->assertEquals('/custom/app/dir', $manager->get('app'));
        $this->assertEquals(sys_get_temp_dir().'/foo', $manager->get('foo'));
    }

    public function configFileInputDataprovider()
    {
        return array(
            array(
                'testconfig1.conf',
                array(
                    'app' => '/test/app',
                    'cache' => '/test/app/cache'
                )
            ),
        );
    }

    /**
     * Test input of a config file
     *
     * @dataProvider configFileInputDataprovider
     */
    public function testConfigFileInput($file, $expected)
    {
        $file = $this->getResourcePath($file);
        $manager = new PathManager($file);

        foreach ($expected as $type => $expectedDir) {
            $actualDir = $manager->get($type);
            $this->assertEquals($expectedDir, $actualDir, "'$actualDir' should be '$expectedDir'");
        }
    }

    public function testFetchFilenameWorks()
    {
        $tempDir = sys_get_temp_dir();
        $expected = $tempDir . '/foo/bar';

        $manager = new PathManager($tempDir);
        $this->assertEquals($expected, $manager->get('root', 'foo/bar'));
    }
}