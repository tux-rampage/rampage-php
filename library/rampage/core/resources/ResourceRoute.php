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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\resources;

use Zend\Mvc\Router\Http\Regex;

/**
 * Resource route
 */
class ResourceRoute extends Regex
{
	/**
     * {@inheritdoc}
     * @see \Zend\Mvc\Router\Http\Regex::__construct()
     */
    public function __construct($path = '_res', array $defaults = array())
    {
        $defaults = array_merge(array(
            'theme' => '',
            'scope' => '',
            'file' => ''
        ), $defaults);

        parent::__construct('/' . $path . '/(?<theme>[a-zA-z0-9_.-]+)/(?<scope>[a-zA-z0-9_.-]+)/(?<file>.+)', $path, $defaults);
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Mvc\Router\Http\Regex::assemble()
     */
    public function assemble(array $params = array(), array $options = array())
    {
        $mergedParams = array_merge($this->defaults, $params);
        $url = '/' . $this->spec
             . '/' . $mergedParams['theme']
             . '/' . ($mergedParams['scope']? $mergedParams['scope'] : '__theme__')
             . '/' . ltrim($mergedParams['file'], '/');

        $this->assembledParams[] = array('theme', 'scope', 'file');
        return $url;
    }

	/**
     * {@inheritdoc}
     * @see \Zend\Mvc\Router\Http\Regex::factory()
     */
    public static function factory($options = array())
    {
        $path = isset($options['path'])? $options['path'] : '_res';
        $defaults = isset($options['defaults'])? $options['defaults'] : array();

        return new self($path, $defaults);
    }
}
