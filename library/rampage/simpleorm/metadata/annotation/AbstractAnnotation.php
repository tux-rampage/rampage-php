<?php
/**
 * This is part of rampage.php
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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm\metadata\annotation;

/**
 * Abstract annotation
 */
abstract class AbstractAnnotation implements AnnotationInterface
{
    /**
     * @var array
     */
    protected $params = array();

    /**
     * @param string $name
     * @param string $default
     * @return mixed
     */
    protected function getParam($name, $default = null)
    {
        if (!isset($this->params[$name])) {
            return $default;
        }

        return $this->params[$name];
    }

    /**
     * @param string $value
     * @return boolean
     */
    protected function toBool($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        $bool = !in_array(strtolower($value), array(
            'no', 'off', 'false', '0', ''
        ));

        return $bool;
    }

    /**
     * @param string $char
     * @return boolean
     */
    private function isQuoteChar($char)
    {
        $result = (($char == '"') || ($char == "'"));
        return $result;
    }

    /**
     * @param string $content
     * @return array
     */
    private function parseContentParts($content)
    {
        $stack = array();
        $end = null;
        $escape = false;
        $item = '';
        $key = false;
        $canUseKey = true;
        $items = array();

        $braces = array(
            '(' => ')',
            '{' => '}',
            '[' => ']'
        );

        for ($offset = 0; $offset < strlen($content); $offset++) {
            $char = $content[$offset];

            if (($char == ',') && ($end === null)) {
                $items[] = array($key, trim($item));
                $item = '';
                $key = false;
                $canUseKey = true;

                continue;
            }

            if ($escape) {
                if (count($stack) || !in_array($char, array('"', '\'', '\\'))) {
                    $item .= '\\';
                }

                $item .= $char;
                $escape = false;
                continue;
            }

            if (($char == '\\') && $this->isQuoteChar($end)) {
                $escape = true;
                continue;
            }

            if (($end !== null) && ($char == $end)) {
                if (count($stack) || !$this->isQuoteChar($end)) {
                    $item .= $char;
                }

                $end = array_pop($stack);
                continue;
            }

            if ($this->isQuoteChar($end)) {
                $item .= $char;
                continue;
            }

            if ($canUseKey && ($char == '=')) {
                $key = trim($item);
                $item = '';
                $canUseKey = false;

                if ($key == '') {
                    $item = '=';
                    $key = false;
                }

                continue;
            }

            if (in_array($char, array('"', '\''))) {
                if ($end !== null) {
                    $stack[] = $end;
                    $item .= $char;
                }

                $canUseKey = false;
                $end = $char;
                continue;
            }

            if (array_key_exists($char, $braces)) {
                if ($end !== null) {
                    $stack[] = $end;
                }

                $canUseKey = false;
                $end = $braces[$char];
            }

            $item .= $char;
        }

        $items[] = array($key, trim($item));
        $items = array_filter($items, function($value) {
            return ($value[1] != '');
        });

        return $items;
    }

    /**
     * @param string $content
     * @param array $optionNames
     * @return array
     */
    protected function parseContent($content, array $optionNames)
    {
        $items = $this->parseContentParts($content);
        $offset = 0;

        foreach ($items as $item) {
            list($key, $value) = $item;

            if ($key === false) {
                if (!isset($optionNames[$offset])) {
                    continue;
                }

                $key = $optionNames[$offset];
                $offset++;
            }

            $this->params[$key] = $value;
        }

        return $this->params;
    }
}