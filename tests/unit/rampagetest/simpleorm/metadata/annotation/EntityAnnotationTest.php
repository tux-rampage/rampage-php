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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampagetest\simpleorm\metadata\annotation;

use rampage\test\AbstractTestCase;
use rampage\simpleorm\metadata\annotation\EntityAnnotation;

/**
 * entity annotation test
 */
class EntityAnnotationTest extends AbstractTestCase
{
    public function namedAnnotationTestDataProvider()
    {
        return array(
            array('table=foo_bar', 'foo_bar'),
            array('  table  =  bar_baz ', 'bar_baz'),
            array('  implicit_table_name', 'implicit_table_name')
        );
    }

    /**
     * @dataProvider namedAnnotationTestDataProvider
     * @param string $content
     * @param string $table
     */
    public function testAnnotationContent($content, $table)
    {
        $annotation = new EntityAnnotation();
        $annotation->initialize($content);

        $this->assertEquals($table, $annotation->getTable());
    }
}
