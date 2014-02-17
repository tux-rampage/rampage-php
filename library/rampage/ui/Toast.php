<?php
/**
 * This is part of rampage-php
 * Copyright (c) 2013 Axel Helmert
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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\ui;

use ArrayObject;

/**
 * @property int $displayTime
 * @property string $message
 * @property string $additionalClass
 */
class Toast extends ArrayObject
{
    /**
     * {@inheritdoc}
     * @see ArrayObject::__construct()
     */
    public function __construct($message, $displayTime, $addditionalCssClass)
    {
        parent::__construct(array(
            'message' => $message,
            'displayTime' => $displayTime,
            'additionalClass' => $addditionalCssClass
        ), self::ARRAY_AS_PROPS);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getMessage();
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return (string)$this->message;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $this->displayTime = (int)$this->displayTime;
        if ($this->displayTime < 1) {
            $this->displayTime = null;
        }

        $options = $this->getArrayCopy();
        unset($options['message']);
        $options = array_filter($options);

        return $options;
    }
}
