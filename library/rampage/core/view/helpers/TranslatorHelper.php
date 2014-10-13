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

use Zend\I18n\View\Helper\Translate as TranslateHelper;

/**
 * Translation helper
 */
class TranslatorHelper extends TranslateHelper
{
    /**
     * (non-PHPdoc)
     * @see \Zend\I18n\View\Helper\Translate::__invoke()
     */
    public function __invoke($message, $arg1 = null, $arg2 = null)
    {
        $args = array_slice(func_get_args(), 1);
        $domain = null;

        if (preg_match('~^([a-z0-9_.-]+)::(.+)$~i', $message, $m)) {
            $domain = $m[1];
            $message = $m[2];
        }

        $message = parent::__invoke($message, $domain);

        if (is_array($args) && (count($args) > 0)) {
            $message = vsprintf($message, $args);
        }

        return $message;
    }
}
