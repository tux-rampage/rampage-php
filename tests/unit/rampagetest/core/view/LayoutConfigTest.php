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
 * @package   rampage.rampagetest
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampagetest\core\view;

use rampage\test\AbstractTestCase;

/**
 * LayoutConfig test case.
 */
class LayoutConfigTest extends AbstractTestCase
{
    /**
     * Layout config
     *
     * @var \rampage\core\view\LayoutConfig
     */
    private $instance;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->instance = null;
        parent::tearDown();
    }

	/**
     * Returns the resources mock
     *
     * @param array $resolverMock
     * @return \rampage\core\view\LayoutConfig
     */
    private function instance($resolverMock = false)
    {
        $this->di()->setConfig(array('mocks' => array(
            'rampage\core\resource\FileLocatorInterface' => array(
                'resolve' => $resolverMock
            )
        )));

        return $this->di()->newInstance('rampage\core\view\LayoutConfig');
    }

    /**
     * Data provider
     */
    public function loadingLayoutDataProvider()
    {
        return array(
            array('test.load1.xml'),
        );
    }

    /**
     * Test loading the layout file works
     *
     * @dataProvider loadingLayoutDataProvider
     * @param string $file
     */
    public function testLoadingLayoutWorks($file)
    {
        $file = $this->getResourcePath($file);
        $instance = $this->instance(array(new \SplFileInfo($file), 'value', 'atleastonce', array('layout', 'test::layout.xml', null, true)));
        $expected = file_get_contents($file);
        $expected = preg_replace('~\?>\s*<layout[^>]+>~sm', '?><layout>', $expected);

        $instance->addFile('test::layout.xml');
        $this->assertXmlStringEqualsXmlString($expected, $instance->getXml()->asXML());
    }

    /**
     * Should return an array as first and second param for each pass
     *
     * The first array should contain filenames as keys and priorities as values.
     * If a priority (value) value for a file is false, the priority param will be omitted when calling addFile()
     *
     * The second array should contain the filenames in the expected order.
     *
     * @return array
     */
    public function addFileDataProvider()
    {
        return array(
            array(array(
                'file1.xml' => 10,
                'file2.xml' => false,
                'file3.xml' => 300,
                'file4.xml' => false
            ), array(
                'file3.xml',
                'file2.xml',
                'file4.xml',
                'file1.xml'
            )),
        );
    }

    /**
     * Tests LayoutConfig->addFile()
     *
     * @dataProvider addFileDataProvider
     */
    public function testAddFile($files, $expectedOrder)
    {
        $actualOrder = array();
        $instance = $this->instance(function($type, $file) use(&$actualOrder) {
            if ($type != 'layout') {
                return false;
            }

            $actualOrder[] = $file;
            return false;
        });

        foreach ($files as $file => $priority) {
            if ($priority === false) {
                $instance->addFile($file);
                continue;
            }

            $instance->addFile($file, $priority);
        }

        $instance->getXml();
        $this->assertEquals($expectedOrder, $actualOrder, 'Config files were not resolved in the requested order');
    }

    /**
     * Test merging multiple files
     *
     * @return array
     */
    public function mergingXmlDataProvider()
    {
        return array(
            array(array('test.merge1/file1.xml', 'test.merge1/file2.xml'), 'test.merge1/expected.xml'),
        );
    }

    /**
     * Test merging multiple XML files works
     *
     * @dataProvider mergingXmlDataProvider
     * @param array $files
     * @param string $expectedXmlFile
     */
    public function testMergingXmlWorks(array $files, $expectedXmlFile)
    {
        $map = array();
        $expectedXmlPath = $this->getResourcePath($expectedXmlFile);
        foreach ($files as $file) {
            $map[$file] = $this->getResourcePath($file);
        }

        $instance = $this->instance(function($type, $file) use ($map) {
            return isset($map[$file])? new \SplFileInfo($map[$file]) : false;
        });

        foreach ($files as $file) {
            $instance->addFile($file);
        }

        $this->assertXmlStringEqualsXmlFile($expectedXmlPath, $instance->getXml()->asXML());
    }

    /**
     * Tests LayoutConfig->getHandle()
     */
    public function testGetHandle()
    {
        $file = $this->getResourcePath('test.gethandle.xml');
        $instance = $this->instance();
        $expectedXml = '
            <handle name="specific.handle">
                <reference name="content">
                    <view type="my.content.view" name="my.content" template="my/content"></view>
                </reference>
            </handle>';

        $instance->setXml(file_get_contents($file));
        $handle = $instance->getHandle('specific.handle');

        $this->assertInstanceOf('rampage\core\xml\SimpleXmlElement', $handle);
        $this->assertEquals('specific.handle', (string)$handle['name']);
        $this->assertXmlStringEqualsXmlString($expectedXml, $handle->asXML());
    }
}

