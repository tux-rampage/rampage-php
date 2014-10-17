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

use rampage\core\url\UrlModelLocator;
use Zend\View\Helper\AbstractHelper as AbstractViewHelper;

/**
 * Advanced URL helper to build FQ URLs
 */
class BaseUrlHelper extends AbstractViewHelper
{
    /**
     * URL model locator
     *
     * @var \rampage\core\url\UrlModelLocator
     */
    protected $urlModelLocator = null;

    /**
     * @param UrlModelLocator $modelLocator
     */
    public function __construct(UrlModelLocator $modelLocator)
    {
        $this->urlModelLocator = $modelLocator;
    }

    /**
     * @return \rampage\core\url\UrlModelInterface
     */
    protected function getUrlModel()
    {
        return $this->urlModelLocator->get('base');
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\View\Helper\Url::__invoke()
     */
    public function __invoke($path = null, array $options = array())
    {
        return $this->getUrlModel()->getUrl($path, $options);
    }
}
