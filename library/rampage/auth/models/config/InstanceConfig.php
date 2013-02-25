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
 * @package   rampage.auth
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\auth\models\config;

use rampage\core\data\Object;
use rampage\auth\models\AdapterInterface;

/**
 * Instance config
 */
class InstanceConfig extends Object implements InstanceConfigInterface
{
    /**
     * (non-PHPdoc)
     * @see \rampage\auth\models\config\InstanceConfigInterface::configure()
     */
    public function configure(AdapterInterface $instance)
    {
        $instance->setOptions($this->getOptions());
        return $this;
    }

    /**
     * get options
     *
     * @return mixed
     */
    public function setOptions()
    {
        if ($this->_has('options')) {
            return $this->_get('options');
        }

        $options = @unserialize((string)$this->getSerializedOptions());
        $this->setOptions($options);

        return $options;
    }

    /**
     * Returns available options
     *
     * @return mixed
     */
    public function getOptions()
    {
        return $this->_get('options');
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\auth\models\config\InstanceConfigInterface::getAdapterType()
     */
    public function getAdapterType()
    {
        return $this->_get('adapter_type');
    }
}