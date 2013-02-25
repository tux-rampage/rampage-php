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

namespace rampagetest\core\modules;

use rampage\test\AbstractTestCase;
use rampage\core\modules\ManifestConfig;

/**
 * Testcase for module manifest loading
 */
class ManifestConfigTest extends AbstractTestCase
{
    public function parsingXmlWorksDataProvider()
    {
        return array(
            array('manifest1.xml', 'manifest1.php'),
        );
    }

    /**
     * Test parsing the xml works
     *
     * @dataProvider parsingXmlWorksDataProvider
     * @param string $xmlFile
     * @param string $expectedFile
     */
    public function testParsingXmlWorks($xmlFile, $expectedFile)
    {
        $xmlFile = $this->getResourcePath($xmlFile);
        $tmpPath = sys_get_temp_dir() . '/' . trim(strtr(__CLASS__, '\\', '.'), '.');
        $expectedFile = $this->getResourcePath($expectedFile);
        $expected = include $expectedFile;

        $moduleMock = $this->mockInterface('rampage\core\modules\ModuleInterface', array(
            'getModulePath' => function($file = null) use ($tmpPath) {
                $path = $tmpPath;
                if ($file) {
                   $path .= '/' . ltrim($file, '/');
                }

                return $path;
            },
        ));

        $instance = new ManifestConfig($moduleMock, $xmlFile);
        $actual = $instance->toArray();

        $this->recursiveArrayKeySort($expected);
        $this->recursiveArrayKeySort($actual);
        $this->assertEquals($expected, $actual);
    }
}