<?php
/**
 * This is part of @application_name@
 * Copyright (c) 2010 Axel Helmert
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
 * @package   @package_name@
 * @copyright Copyright (c) 2010 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\xml;

use ArrayIterator;

/**
 * Simple XML extensions
 */
class SimpleXmlElement extends \SimpleXMLElement
{
    /**
     * merge action replace
     */
    const MERGE_REPLACE = 1;

    /**
     * merge action append
     */
    const MERGE_APPEND = 2;

    /**
     * Check for child elements
     *
     * @return bool
     */
    public function hasChildren()
    {
        foreach ($this->children() as $child) {
            return true;
        }

        return false;
    }

    /**
     * Get boolean value
     *
     * @param string $value
     * @return bool
     */
    protected function _toBoolean($value)
    {
        $value = strtolower((string)$value);
        $result = ($value != 'false')
                && ($value != 'off')
                && !empty($value);

        return $result;
    }

    /**
     * Check child/attribute
     *
     * @param string $name
     * @param bool|string $default
     * @return bool
     */
    public function is($name, $default = true)
    {
        $value = (isset($this->{$name}))?
                    (string)$this->{$name} :
                    (isset($this[$name]))?
                        (string)$this[$name] : null;


        if (is_bool($default)) {
            if ($value === null) {
                return $default;
            }

            $result = $this->_toBoolean($value);
        } else {
            $result = (strcasecmp($value, $default) === 0);
        }

        return $result;
    }

    /**
     * Inject namespaces
     *
     * @param sting $xpath
     * @param string $ns
     */
    private function injectNsToXpathQuery($xpath, $ns)
    {
        $literals = array();
        $offset = 0;
        $keywords = array(
            '$$_AND_$$' => 'and',
            '$$_OR_$$' => 'or'
        );

        // identified escaped backslashes
        $extractor = function($m) use (&$literals, &$offset) {
            $placeholder = '$$_' . $offset . '_$$';
            $literals[$placeholder] = $m[0];
            $offset++;

            return $placeholder;
        };

        // Extract literals
        $modified = preg_replace_callback('~(\'|").*?\1~is', $extractor, $xpath);
        $modified = preg_replace_callback('~\b(and|or)\b~i', function($match) {
            return '$$_' . strtoupper(trim($match[1])) . '_$$';
        }, $modified);

        // Now find all unqualified names and prefix them with the namespace
        $modified = preg_replace('~(?<!@|:)\b[a-z][a-z0-9_-]*\b(?!:|\s*\()~i', $ns . ':$0', $modified);

        // re-insert keywords and literals
        $modified = strtr($modified, $keywords);
        $modified = strtr($modified, $literals);

        return $modified;
    }

    /**
     * Quote the given value for an xpath expression
     *
     * This will also automatically enclose the string
     *
     * @param string $string
     * @return string
     */
    public static function quoteXpathValue($string)
    {
        if (strpos($string, "'") === false) {
            return "'$string'";
        }

        if (strpos($string, '"') === false) {
            return '"' . $string . '"';
        }

        // String contains ' and " -> need to use concat ...
        $parts = explode("'", $string);
        $quoted = implode('\', "\'", \'', $parts);

        return "concat('$quoted')";
    }

    /**
     * (non-PHPdoc)
     * @see SimpleXMLElement::xpath()
     * @return \ArrayIterator
     */
    public function xpath($path, $skipNsSanitizing = false)
    {
        try {
            if (!$skipNsSanitizing) {
                // XPath has some trouble with unqualified node names when
                // default xmlns is set.
                // try to find out if namespaces should be registered and
                // if the xpath must be sanitized (i.E. default xmlns)
                foreach ($this->getNamespaces() as $prefix => $ns) {
                    if ($prefix == '') {
                        $prefix = 'ns';
                        $path = $this->injectNsToXpathQuery($path, $prefix);
                    }

                    $this->registerXPathNamespace($prefix, $ns);
                }
            }

            $result = @parent::xpath($path);
            if ($result === false) {
                $last = error_get_last();
                $error = (isset($last['message']))? "\n" . $last['message'] : '';
                $code  = (isset($last['type']))? $last['type'] : 0;
                $xmlErrors = '';

                foreach (libxml_get_errors() as $err) {
                    $xmlErrors .= "\n" . $err->message;
                }

                $message = sprintf(
                    "Failed to process xpath \"%s\"%s%s",
                    $path, $error, $xmlErrors
                );

                throw new exception\RuntimeException($message);
            }
        } catch (exception\RuntimeException $e) {
            // Don't convert to runtime exception again
            throw $e;
        } catch (\Exception $e) {
            throw new exception\RuntimeException($e);
        }

        if (!is_array($result)) {
            $result = array();
        }

        $result = new ArrayIterator($result);
        return $result;
    }

    /**
     * returns the parent element
     *
     * @return \rampage\core\xml\SimpleXmlElement|null
     */
    public function getParent()
    {
        $parent = $this->xpath('..', true)->current();
        return $parent;
    }

    /**
     * returns the path name
     *
     * @return string
     */
    public function getPath()
    {
        $stack = array($this->getName());
        $current = $this;

        while ($parent = $current->getParent()) {
            array_unshift($stack, $parent->getName());
            $current = $parent;
        }

        return implode('/', $stack);
    }

    /**
     * Set node value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this[0] = (string)$value;
        return $this;
    }

    /**
     * To php value
     *
     * @param string $type
     * @return mixed
     */
    public function toValue($type = null, $valueAttribute = null)
    {
        $value = ($valueAttribute)? (string)$this[$valueAttribute] : (string)$this;
        $type = ($type)? (string)$this['type'] : $type;

        switch ($type) {
            case 'int':
                $value = (int)$value;
                break;

            case 'float':
                $value = (float)$value;
                break;

            case 'bool':
                $value = $this->_toBoolean($value);
                break;

            case 'double':
                $value = (double)$value;
                break;

            case 'null':
                $value = null;
                break;

            case 'array':
                $value = $this->toArray(true);
                break;
        }

        return $value;
    }

    /**
     * convert to array
     *
     * @return string
     */
    public function toArray($force = true)
    {
        if (!$force && !$this->hasChildren()) {
            return $this->toValue();
        }

        $data = array();
        foreach ($this->children() as $name => $child) {
            $data[$name] = $child->toArray(false);
        }

        return $data;
    }

    /**
     * Convert to php value
     *
     * @param string $type
     * @return mixed
     */
    public function toPhpValue($type = null, $serviceLocator = null)
    {
        /* @var $serviceLocator \rampage\core\ObjectManagerInterface */
        if (!$type) {
            $type = $this->getName();
        }

        switch ($type) {
            case 'array':
                $value = array();
                if (!isset($this->item)) {
                    return $value;
                }

                foreach ($this->item as $item) {
                    if (!($type = (string)$item['type'])) {
                        $type = 'string';
                    }

                    $current = null;
                    if ($item->$type) {
                        $current = $item->{$type}->toPhpValue($type, $serviceLocator);
                    }

                    if (!isset($item['key']) && !isset($item['index'])) {
                        $value[] = $current;
                        continue;
                    }

                    $key = (isset($item['key']))? (string)$item['key'] : intval((string)$item['index']);
                    $value[$key] = $current;
                }

                break;

            case 'null':
                $value = null;
                break;

            case 'instance':
                if (!is_callable(array($serviceLocator, 'get'))
                  || !is_callable(array($serviceLocator, 'has'))) {
                    return null;
                }

                $class = (string)$this['class'];
                if (!$class || !$serviceLocator->has($class)) {
                    return null;
                }

                // DI/Object manager?
                if (isset($this->options) && is_callable(array($serviceLocator, 'newInstance'))) {
                    $options = $this->options->toPhpValue('array', $serviceLocator);
                    $value = $serviceLocator->newInstance($class, $options);
                } else {
                    $value = $serviceLocator->get($class);
                }

                break;

            default:
                $value = $this->toValue($type);
                break;
        }

        return $value;
    }

    /**
     * find merge rule
     *
     * @param \rampage\core\xml\SimpleXmlElement $element
     * @param \rampage\core\xml\SimpleXmlElement $affected
     * @return int
     */
    protected function _getMergeRule($element, &$affected = null, $rule = null)
    {
        if ($rule instanceof MergeRuleInterface) {
            $result = $rule($this, $element, $affected);

            if ($result !== false) {
                return $result;
            }
        }

        $name = $element->getName();
        if (isset($this->{$name})) {
            $affected = $this->{$name};
            return self::MERGE_REPLACE;
        }

        return self::MERGE_APPEND;
    }

    /**
     * Merge attributes from the given node to this one
     *
     * @param \rampage\core\xml\SimpleXmlElement $node
     * @param bool $replace
     */
    public function mergeAttributes(SimpleXmlElement $node, $replace = true)
    {
        foreach ($node->attributes() as $name => $value) {
            if (isset($this[$name])) {
                if ($replace) {
                    $this[$name] = (string)$value;
                }

                continue;
            }

            $this->addAttribute($name, (string)$value);
        }

        return $this;
    }

    /**
     * merge another xml element into this one
     *
     * @param SimpleXmlElement $element
     * @return string
     */
    public function merge(SimpleXmlElement $element, $replace = true, $rule = null)
    {
        foreach ($element->children() as $name => $child) {
            $affected = null;
            $currentpath = $child->getPath();
            $action = $this->_getMergeRule($child, $affected, $rule);

            if ($action == self::MERGE_APPEND) {
                $affected = $this->addChild($name, (string)$child);
            }

            if (!$affected instanceof SimpleXmlElement) {
                continue;
            }

            $affected->mergeAttributes($child, $replace);

            if ($child->hasChildren()) {
                $affected->merge($child, $replace, $rule);
                continue;
            } else if ($replace) {
                $affected[0] = (string)$child;
            }
        }

        return $this;
    }
}