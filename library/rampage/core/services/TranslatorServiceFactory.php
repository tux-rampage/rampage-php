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

namespace rampage\core\services;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\I18n\Translator\Translator;

/**
 * Translator service factory
 */
class TranslatorServiceFactory implements FactoryInterface
{
    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $locale \rampage\core\i18n\Locale */
        $config = $serviceLocator->get('Config');
        $locale = $serviceLocator->get('locale');
        $translatorConfig = isset($config['translator']) ? $config['translator'] : array();
        $fallback = $locale->getTranslatorFallback();
        $translator = Translator::factory($translatorConfig);

        if (isset($translatorConfig['locale'])) {
            $fallback = $translatorConfig['locale'];
            if (is_array($fallback)) {
                $fallback = array_pop($fallback);
            }
        }

        $translator->setFallbackLocale($fallback);
        $translator->setLocale($locale->getTranslatorLocale());

        return $translator;
    }
}