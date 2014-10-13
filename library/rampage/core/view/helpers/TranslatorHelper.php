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

use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use Zend\I18n\Exception\RuntimeException;

/**
 * Translation helper
 */
class TranslatorHelper extends AbstractTranslatorHelper
{
    /**
     * (non-PHPdoc)
     * @see \Zend\I18n\View\Helper\Translate::__invoke()
     */
    public function __invoke($message)
    {
        $args = array_slice(func_get_args(), 1);
        $domain = null;
        $m = array();

        if (preg_match('~^([a-z0-9_.-]+)::(.+)$~i', $message, $m)) {
            $domain = $m[1];
            $message = $m[2];
        }

        $translator = $this->getTranslator();

        if (null === $translator) {
            throw new RuntimeException('Translator has not been set');
        }

        if (null === $domain) {
            $domain = $this->getTranslatorTextDomain();
        }

        $message = $translator->translate($message, $domain);

        if (is_array($args) && (count($args) > 0)) {
            $message = vsprintf($message, $args);
        }

        return $message;
    }
}
