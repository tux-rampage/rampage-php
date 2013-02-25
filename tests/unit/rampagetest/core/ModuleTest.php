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

use rampage\test\AbstractTestCase;
use rampage\core\Module;
use GlobIterator;

/**
 * Module test
 */
class ModuleTest extends AbstractTestCase
{
    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
    }

	/**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        parent::tearDown();

        /* @var $file \SplFileInfo */
        $iterator = new GlobIterator(sys_get_temp_dir() . '/ModuleTest_*');
        foreach ($iterator as $file) {
            if ($file->isDir() && !$file->isLink()) {
                continue;
            }

            @unlink($file->getPathname());
        }
    }

	/**
     * Test loading from XML
     */
    public function testLoadFromXmlWithAbsolutePath()
    {
        $this->markTestIncomplete('TODO Implement load from XML utilizes class');
    }

    /**
     * Test compiling the manifest works
     */
    public function testCompileManifest()
    {
        $path = $this->getResourcePath('modtest1');
        $module = new Module('mod.test1', array('_path' => $path));
        $file = sys_get_temp_dir() . '/ModuleTest_manifest.php';

        if (file_exists($file)) {
            unlink($file);
        }

        $module->load();
        $module->compileManifest($file);

        $this->assertFileExists($file);
    }
}