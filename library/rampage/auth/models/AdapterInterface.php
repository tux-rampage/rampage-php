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

namespace rampage\auth\models;

use Zend\Authentication\Adapter\AdapterInterface as ZendAdapterInterface;

/**
 * Auth adapter interface
 */
interface AdapterInterface extends ZendAdapterInterface
{
    /**
     * A readable frontend label for this adapter
     *
     * @return string
     */
    public function getLabel();

    /**
     * Adapter code to identify this adapter type
     *
     * i.e. ldap, db, openid, etc ...
     *
     * @return string
     */
    public function getCode();

    /**
     * The config implementation to use
     *
     * @return string
     */
    public function getConfigClass();

    /**
     * The name of the config layout to render
     *
     * @return string
     */
    public function getConfigLayout();

    /**
     * The view layout to display when the user needs to authenticate
     *
     * @return string
     */
    public function getAuthLayout();

    /**
     * Set options
     *
     * @param array $options
     */
    public function setOptions($options);
}