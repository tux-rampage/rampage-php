<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2013 Axel Helmert
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
 * @package   rampagetest.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampagetest\simpleorm\metadata\annotation;

use rampage\simpleorm\metadata\annotation\ClassAnnotationManager;
use rampage\test\AbstractTestCase;
use Zend\Code\Scanner\AnnotationScanner;
use Zend\Code\NameInformation;

/**
 * Class annotation manager test
 */
class ClassAnnotationManagerTest extends AbstractTestCase
{
    /**
     * Test parsing annotation data with zend annotation scanner
     */
    public function testParseAnnotations()
    {
        $instance = new ClassAnnotationManager();
        $comment = '/**
        * @orm:entity(table=foo)
        * @orm:field(property=some_data field=some_field)
        */';

        $scanner = new AnnotationScanner($instance, $comment, new NameInformation('rampage\foo\bar'));

        $this->assertTrue($scanner->hasAnnotation('rampage\simpleorm\metadata\annotation\EntityAnnotation'));
        $this->assertTrue($scanner->hasAnnotation('rampage\simpleorm\metadata\annotation\FieldAnnotation'));
    }
}