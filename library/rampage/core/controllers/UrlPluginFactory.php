<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2012 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 * @version   $Id$
 */

namespace rampage\core\controllers;

use rampage\core\services\DIPluginServiceFactory;

/**
 * Url plugin factory
 */
class UrlPluginFactory extends DIPluginServiceFactory
{
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct('rampage\core\controllers\UrlPlugin');
    }
}
