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
 * @package   rampage.auth
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\auth\service;

/**
 * Adapter service config
 */
class AdapterConfig implements AdapterConfigInterface
{
    /**
     * Config data
     *
     * @var array
     */
    private $data = array();

	/**
     * Construct
     *
     * @service config $config
     * @param array|\ArrayAccess $config
     */
    public function __construct($config)
    {
        if (!isset($config['rampage']['auth']['adapters'])) {
            return;
        }

        $data = $config['rampage']['auth']['adapters'];
        if (is_array($data) || ($data instanceof \Traversable)) {
            $this->data = $data;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\auth\service\AdapterConfigInterface::configure()
     */
    public function configure(\rampage\auth\service\AuthAdapterManager $manager)
    {
        foreach ($this->data as $type => $class) {
            $manager->addType($type, $class);
        }

        return $this;
    }
}