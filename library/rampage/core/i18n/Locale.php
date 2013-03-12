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

namespace rampage\core\i18n;

/**
 * Locale
 */
class Locale
{
    /**
     * Default locale
     *
     * @var string
     */
    protected static $default = 'en_US';

    /**
     * Current locale
     *
     * @var string
     */
    protected $locale = null;

    /**
     * Construct
     *
     * @param string $locale
     */
    public function __construct($locale)
    {
        $this->setLocale($locale);
    }

    /**
     * Get the language from the given locale
     *
     * @param string $locale
     * @return string
     */
    protected function getLanguageFromLocale($locale)
    {
        if (extension_loaded('intl')) {
            return \Locale::getPrimaryLanguage($locale);
        }

        return $this->parse($locale, 'language');
    }

    /**
     * Fetch the region from the given locale
     *
     * @param string $locale
     * @return string
     */
    protected function getRegionFromLocale($locale)
    {
        if (extension_loaded('intl')) {
            return \Locale::getRegion($locale);
        }

        return $this->parse($locale, 'region');
    }

    /**
     * Parse locale
     *
     * @param string $locale
     * @param string $returnType
     * @return array
     */
    protected function parse($locale, $returnType = null)
    {
        if (extension_loaded('intl')) {
            $info = \Locale::parseLocale($locale);
        } else {
            $info = array();

            if (!preg_match('~^(?<language>[a-z]{2,4})([_-](?<region>[a-z]{2,4}))?$~i', $locale, $info)) {
                return null;
            }

            if (isset($info['region'])) {
                $info['region'] = strtoupper($info['region']);
            }
        }

        if ($returnType !== null) {
            $value = ($info && isset($info[$returnType]))? $info[$returnType] : null;
            return $value;
        }

        return $info;
    }

    /**
     * Find the region for the current language
     *
     * @return string
     */
    protected function findRegion()
    {
        if ($this->locale) {
            $currentLang = $this->getLanguageFromLocale($this->locale);
            $defaultLang = $this->getLanguageFromLocale(static::$default);

            if ($currentLang == $defaultLang) {
                $region = $this->getRegionFromLocale(static::$default);

                if ($region) {
                    return $region;
                }
            }
        }

        $region = strtoupper($this->getLanguage());
        if ($region == 'EN') {
            $region = 'US';
        }

        return $region;
    }

    /**
     * Set the locale
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Returns the locale
     *
     * @return string
     */
    public function getLocale()
    {
        if ($this->locale !== null) {
            return $this->locale;
        }

        return static::$default;
    }

    /**
     * Returns the region code
     */
    public function getRegion()
    {
        $region = $this->getRegionFromLocale($this->getLocale());
        if (!$region) {
            $region = $this->findRegion();
        }

        return $region;
    }

    /**
     * Returns the primary language
     *
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->getLanguageFromLocale($this->getLocale());
    }

    /**
     * Returns the locale for the translator
     *
     * @return string
     */
    public function getTranslatorLocale()
    {
        return $this->getLanguage() . '-' . $this->getRegion();
    }

    /**
     * Returns the translator fallback
     *
     * @return string
     */
    public function getTranslatorFallback()
    {
        return $this->getLanguageFromLocale(static::$default) . '-' . $this->getRegionFromLocale(static::$default);
    }

    /**
     * Set the default locale
     *
     * @param string $locale
     */
    public static function setDefault($locale)
    {
        static::$default = $locale;

        if (extension_loaded('intl')) {
            \Locale::setDefault($locale);
        }
    }

    /**
     * Default locale
     *
     * @return string
     */
    public static function getDefault()
    {
        return static::$default;
    }
}