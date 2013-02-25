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
use rampage\core\PathManager;
use rampage\core\ModuleRegistry;

class ModuleRegistryTest extends AbstractTestCase
{
    /**
     * Initialize path manager
     *
     * @param string $dir
     * @return \rampage\core\PathManager
     */
    protected function initPathManager($dir)
    {
        return new PathManager(array(
            'root' => $this->getResourcePath($dir),
            'etc' => $this->getResourcePath($dir),
            'modules' => $this->getResourcePath($dir . '/modules')
        ));
    }

    /**
     * Create a test instance
     *
     * @param string $scope
     * @return \rampage\core\ModuleRegistry
     */
    protected function createTestInstance($scope)
    {
        $pathManager = $this->initPathManager($scope);
        $registry = new ModuleRegistry();
        $registry->setPathManager($pathManager);

        return $registry;
    }

    /**
     * Test initializing modules from config
     */
    public function testInitModulesFromConfig()
    {
        $registry = $this->createTestInstance('inittest1');
        $expected = array('foo.bar');
        $this->assertEquals($expected, $registry->getModuleNames());
    }

    /**
     * Test creationg a compiled php config
     */
    public function testCreatePhpConfig()
    {
        $registry = $this->createTestInstance('inittest1');
        $file = sys_get_temp_dir() . '/modules.compiletest.php';

        $registry->initModules()->saveStaticDefinition($file);
        $this->assertFileExists($file);
        $result = include $file;

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('foo.bar', $result);
        $this->assertInstanceOf('\rampage\core\Module', $result['foo.bar']);
    }
}