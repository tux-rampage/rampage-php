<?php
/**
 * Copyright (c) 2014 Axel Helmert
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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\view\helpers;

use rampage\core\BaseUrl;

use Zend\View\Helper\AbstractHelper as AbstractViewHelper;


/**
 * Advanced URL helper to build FQ URLs
 */
class BaseUrlHelper extends AbstractViewHelper
{
    /**
     * @var BaseUrl
     */
    protected $baseUrl = null;

    /**
     * @param UrlModelLocator $modelLocator
     */
    public function __construct($baseUrl = null)
    {
        if (!$baseUrl instanceof BaseUrl) {
            $baseUrl = new BaseUrl($baseUrl);
        }

        $this->baseUrl = $baseUrl;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\View\Helper\Url::__invoke()
     */
    public function __invoke($path = null, $options = null)
    {
        if ($path === null) {
            return $this->baseUrl;
        }

        return $this->baseUrl->getUrl($path, $options);
    }
}
