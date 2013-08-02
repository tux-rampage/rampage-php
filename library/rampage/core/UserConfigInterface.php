<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2012 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 * @version   $Id$
 */

namespace rampage\core;

/**
 * Interface for user config
 */
interface UserConfigInterface
{
    /**
     * This method should set the current config domain
     *
     * @param string $name
     * @return self
     */
    public function setDomain($name);

    /**
     * This method should return the requested config value
     *
     * @param string $name
     * @param mixed $default
     * @param string $domain
     * @return mixed
     */
    public function getConfigValue($name, $default = null, $domain = null);
}