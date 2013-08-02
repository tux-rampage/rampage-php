<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2012 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 * @version   $Id$
 */

namespace rampage\core\url;

/**
 * Interface for URL configs
 */
interface UrlConfigInterface
{
    /**
     * This method should configure the given URL Model
     *
     * @param UrlModel $url
     */
    public function configureUrlModel(UrlModelInterface $url);
}