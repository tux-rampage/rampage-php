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

namespace rampage\core\data;

use rampage\core\exception;

/**
 * Data object
 */
class ValueObject extends AbstractValueObject
{
    /**
     * Magic call implementation for unknown methods.
     *
     * Provides get/set/has and remove methods for data.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (strlen($method) > 3)  {
            $type = substr($method, 0, 3);
            $name = substr($method, 3);

            switch ($type) {
                case 'get':
                    $default = isset($args[0])? $args[0] : null;
                    return $this->get($this->underscore($name), $default);
                    break;

                case 'set':
                    $arg = (isset($args[0]))? $args[0] : null;
                    return $this->set($this->underscore($name), $arg);
                    break;

                case 'has':
                    return $this->has($this->underscore($name));
                    break;

                case 'uns':
                    if ((strlen($method) > 5) && (substr($method, 0, 5) == 'unset')) {
                        $name = $this->underscore(substr($method, 5));
                        $this->remove($name);
                        return $this;
                    }

                    break;
            } // end switch($type)
        }

        throw new exception\RuntimeException('Failed to call method ' . $method);
    }
}
