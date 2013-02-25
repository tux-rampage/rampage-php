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
 * @package   rampage.test
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\test\di;

use Zend\Di\Config as DefaultConfig;
use Zend\Di\Di as DiContainer;
use ArrayAccess;

/**
 * Di container config
 */
class Config extends DefaultConfig
{
    /**
     * (non-PHPdoc)
     * @see \Zend\Di\Config::__construct()
     */
    public function __construct($options)
    {
        if (!is_array($options) && !($options instanceof ArrayAccess)) {
            return parent::__construct(array());
        }

        parent::__construct($options);
    }

	/**
     * (non-PHPdoc)
     * @see \Zend\Di\Config::configure()
     */
    public function configure(DiContainer $di)
    {
        if (($di instanceof Di) && isset($this->data['mocks'])) {
            $di->addMockDefinitions($this->data['mocks']);
        }

        return parent::configure($di);
    }
}
