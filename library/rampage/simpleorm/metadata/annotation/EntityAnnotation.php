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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm\metadata\annotation;

/**
 * Entity annotation
 */
class EntityAnnotation implements AnnotationInterface
{
    protected $table = null;
    protected $name = null;

    /**
     * @see \rampage\simpleorm\metadata\annotation\AnnotationInterface::getKeyword()
     */
    public function getKeyword()
    {
        return 'entity';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @see \Zend\Code\Annotation\AnnotationInterface::initialize()
     */
    public function initialize($content)
    {
        if (preg_match('\bname\s*=\s*([a-zA-Z0-9.\\_]+)', $content, $m)) {
            $this->name = $m[1];
        }

        if (preg_match('\btable\s*=\s*([a-zA-Z0-9_]+)', $content, $m)) {
            $this->table = $m[1];
        }
    }
}
