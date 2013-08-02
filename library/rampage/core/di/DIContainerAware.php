<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2012 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 * @version   $Id$
 */

namespace rampage\core\di;

use Zend\Di\Di;

/**
 * Interface for classes that needs to consume the DI container
 */
interface DIContainerAware
{
    /**
     * @param Di $container
     */
    public function setDIContainer(Di $container);
}