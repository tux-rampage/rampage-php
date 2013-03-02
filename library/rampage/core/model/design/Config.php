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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\model\design;

use Traversable;
use ArrayAccess;

/**
 * Design config
 */
class Config
{
    /**
     * Config
     *
     * @var array
     */
    private $data = array();

    /**
     * Construct
     *
     * @param array $data
     */
    public function __construct($data = array())
    {
        if (!isset($data['rampage']['themes'])
          && (!is_array($data['rampage']['themes'])
          || !($data['rampage']['themes'] instanceof ArrayAccess))) {
            return;
        }

        $this->data = $data['rampage']['themes'];
    }

    /**
     * Retrieve the fallback theme
     *
     * @param string $name
     * @return array
     */
    public function getFallbackThemes($name)
    {
        if (!isset($this->data[$name]['fallbacks'])) {
            return array();
        }

        if (!is_array($this->data[$name]['fallbacks'])
          && !($this->data[$name]['fallbacks'] instanceof Traversable)) {
            return array((string)$this->data[$name]['fallbacks']);
        }

        return $this->data[$name]['fallbacks'];
    }
}