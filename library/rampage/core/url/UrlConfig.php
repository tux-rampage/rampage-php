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

namespace rampage\core\url;

use rampage\core\ArrayConfig;


class UrlConfig extends ArrayConfig implements UrlConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function configureUrlModel(UrlModelInterface $url)
    {
        $type = $url->getType()? : 'base';
        $unsecureUrl = $this->getSection($type)->get('unsecure');
        $secureUrl = $this->getSection($type)->get('secure');

        if ($unsecureUrl) {
            $url->setBaseUrl($unsecureUrl, false);
        }

        if ($secureUrl) {
            $url->setBaseUrl($secureUrl, true);
        } else if ($unsecureUrl) {
            $url->setBaseUrl($unsecureUrl, true);
        }

        return $this;
    }
}
