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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\view\helpers;

use Zend\View\Helper\AbstractHelper;
use rampage\core\resources\UrlLocatorInterface;
use rampage\core\exception\RuntimeException;

/**
 * Resource URL locator helper
 */
class ResourceUrlHelper extends AbstractHelper
{
    /**
     * URL locator
     *
     * @var \rampage\core\resource\UrlLocatorInterface
     */
    private $locator = null;

    /**
     * Construct
     *
     * @param UrlLocatorInterface $urlLocator
     */
    public function __construct(UrlLocatorInterface $urlLocator)
    {
        $this->locator = $urlLocator;
    }

    /**
     * URL locator
     *
     * @return \rampage\core\resource\UrlLocatorInterface
     */
    protected function getUrlLocator()
    {
        return $this->locator;
    }

    /**
     * Invoke helper
     *
     * @param string $file
     * @param string $scope
     */
    public function __invoke($file, $scope = null)
    {
        try {
            return $this->getUrlLocator()->getUrl($file, $scope);
        } catch (RuntimeException $exception) {
            return 'not-found';
        }
    }
}