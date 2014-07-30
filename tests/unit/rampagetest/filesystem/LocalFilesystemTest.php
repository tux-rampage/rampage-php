<?php
/**
 * This is part of rampage-php
 * Copyright (c) 2014 Axel Helmert
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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampagetest\filesystem;

use rampage\test\AbstractTestCase;
use rampage\filesystem\LocalFilesystem;
use RecursiveIteratorIterator;

/**
 * @coversDefaultClass \rampage\filesystem\LocalFilesystem
 */
class LocalFilesystemTest extends AbstractTestCase
{
    private $instance = null;

    /**
     * {@inheritdoc}
     * @see \rampage\test\AbstractTestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->instance = null;
        parent::tearDown();
    }

	/**
     * {@inheritdoc}
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        $this->instance = new LocalFilesystem($this->getResourcePath('readonly'));
    }

    /**
     * @return array
     */
    public function existingFilesDataProvider()
    {
        return array(
            array('textfile.txt'),
            array('subdir/subfile1.txt'),
            array('subdir/subfile2.txt'),
            array('foo/bar/baz.file'),
            array('foo/bar'),
            array('foo'),
            array('subdir'),
        );
    }

    /**
     * @return array
     */
    public function nonExistingFilesDataProvider()
    {
        return array(
            array('nosuchfile.txt'),
            array('subdir/nope.txt'),
            array('this/does/not/exist.file'),
            array('foo/bar.baz'),
            array('subdir/foo/bar'),
            array('subdir/foo'),
            array('asjd?ahsdu&/(sad'),
        );
    }

    /**
     * @covers ::__construct
     */
    public function testConstructWithInvalidDirThrowsException()
    {
        $this->setExpectedException('RuntimeException');
        new LocalFilesystem('+sjkd?/does/not/exist/shd8132u1##');
    }

    /**
     * @covers ::__construct
     */
    public function testConstructWithFilepathThrowsException()
    {
        $this->setExpectedException('RuntimeException');
        new LocalFilesystem($this->getResourcePath('file.dummy'));
    }

    /**
     * @covers ::__construct
     */
    public function testConstructWithValidPath()
    {
        $filesystem = new LocalFilesystem($this->getResourcePath('readonly'));
        $this->assertTrue($filesystem->valid());
    }

    /**
     * @covers ::info
     */
    public function testInfoWorksWithExisting()
    {
        $info = $this->instance->info('textfile.txt');

        $this->assertInstanceOf('rampage\filesystem\FileInfoInterface', $info);
        $this->assertTrue($info->exists());
    }

    /**
     * @covers ::info
     */
    public function testInfoWorksWithNonExisting()
    {
        $info = $this->instance->info('nosuchfile.txt');

        $this->assertInstanceOf('rampage\filesystem\FileInfoInterface', $info);
        $this->assertFalse($info->exists());
    }

    /**
     * @covers ::next
     * @covers ::current
     * @covers ::key
     * @covers ::valid
     * @covers ::rewind
     * @covers ::info
     * @covers ::accept
     */
    public function testIteratorImplementation()
    {
        $result = array_keys(iterator_to_array($this->instance, true));
        $expected = array('subdir', 'textfile.txt');

        sort($result);
        sort($expected);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::next
     * @covers ::current
     * @covers ::key
     * @covers ::valid
     * @covers ::rewind
     * @covers ::info
     * @covers ::accept
     * @covers ::hasChildren
     * @covers ::getChildren
     */
    public function testRecursiveIteratorImplementation()
    {
        $iterator = new RecursiveIteratorIterator($this->instance, RecursiveIteratorIterator::SELF_FIRST);
        $result = array_keys(iterator_to_array($iterator, true));
        $expected = array(
            'foo',
            'foo/bar',
            'foo/bar/baz',
            'foo/bar/baz.file',
            'subdir',
            'subdir/subfile1.txt',
            'subdir/subfile2.txt',
            'textfile.txt'
        );

        sort($result);
        sort($expected);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::offsetSet
     */
    public function testAddArrayElementFails()
    {
        $this->setExpectedException('LogicException');
        $this->instance['newfile'] = 'Content of new file';
    }

    /**
     * @covers ::offsetUnset
     */
    public function testRemoveArrayElementFails()
    {
        $this->setExpectedException('LogicException');
        unset($this->instance['textfile.txt']);
    }

    /**
     * @dataProvider existingFilesDataProvider
     * @covers ::offsetExists
     */
    public function testOffsetExistsWithExistingFile($file)
    {
        $this->assertTrue($this->instance->offsetExists($file));
    }

    /**
     * @dataProvider nonExistingFilesDataProvider
     * @covers ::offsetExists
     */
    public function testOffsetExistsWithNonExistingFile($file)
    {
        $this->assertFalse($this->instance->offsetExists($file));
    }
}
