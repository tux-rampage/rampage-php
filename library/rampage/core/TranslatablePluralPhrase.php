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


class TranslatablePluralPhrase extends TranslatablePhrase
{
    /**
     * @var string
     */
    protected $pluralText = '';

    /**
     * @var int
     */
    protected $number = 0;

    /**
     * @var array
     */
    protected $args = [];

    /**
     * @param string $text
     * @param ... $args
     */
    public function __construct($text, $pluralText, $number)
    {
        $this->text = (string)$text;
        $this->pluralText = (string)$pluralText;
        $this->args = array_slice(func_get_args(), 2);
    }

    /**
     * @return string
     */
    public function getTranslatedText()
    {
        if ($this->translator && ($this->text != '')) {
            return (string)$this->translator->translatePlural($this->text, $this->pluralText, $this->number, $this->getTextDomain());
        }

        return $this->getText();
    }

    /**
     * @return string
     */
    public function getText()
    {
        if ($this->number != 1) {
            return $this->pluralText; // Plural
        }

        return $this->text; // Singular
    }

    /**
     * @return number
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param number $number
     * @return self
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @param array $args
     * @return self
     */
    public function setArgs(array $args)
    {
        parent::setArgs($args);
        array_unshift($this->args, $this->number);

        return $this;
    }

    /**
     * @return self
     */
    public function clearArgs()
    {
        $this->args = [ $this->number ];
        return $this;
    }
}
