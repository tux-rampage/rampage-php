<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core;

use Zend\I18n\Translator\Translator;


class TranslatablePhrase implements i18n\TranslatorAwareInterface
{
    /**
     * @var Translator
     */
    protected $translator = null;

    /**
     * @var string
     */
    protected $text = '';

    /**
     * @var string
     */
    protected $textDomain = null;

    /**
     * @var array
     */
    protected $args = [];

    /**
     * {@inheritdoc}
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param string $text
     * @param ... $args
     */
    public function __construct($text)
    {
        $this->text = (string)$text;
        $this->args = array_slice(func_get_args(), 1);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->toString();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @return string
     */
    public function toString()
    {
        $text = $this->getTranslatedText();

        if (!empty($this->args)) {
            $text = vsprintf($text, $this->args);
        }

        return $text;
    }

    /**
     * @return string
     */
    public function getTranslatedText()
    {
        if ($this->translator && ($this->text != '')) {
            return (string)$this->translator->translate($this->text, $this->getTextDomain());
        }

        return $this->text;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return self
     */
    public function setText($text)
    {
        $this->text = (string)$text;
        return $this;
    }

    /**
     * @return string
     */
    public function getTextDomain()
    {
        return $this->textDomain;
    }

    /**
     * @param string $textDomain
     * @return self
     */
    public function setTextDomain($textDomain)
    {
        $this->textDomain = ($textDomain == '')? 'default' : (string)$textDomain;
        return $this;
    }

    /**
     * @return multitype:
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param array $args
     * @return self
     */
    public function setArgs(array $args)
    {
        $this->args = array_values($args);
        return $this;
    }

    /**
     * @return self
     */
    public function clearArgs()
    {
        $this->args = [];
        return $this;
    }
}
