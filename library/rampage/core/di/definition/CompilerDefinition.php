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

namespace rampage\core\di\definition;

use Zend\Di\Definition\CompilerDefinition as ZendCompilerDefinition;
use Zend\Code\Reflection\MethodReflection;
use ReflectionParameter as ParameterReflection;
use Zend\Code\Reflection\ClassReflection;

/**
 * Compiler di definition
 *
 * Allows simple @service annotations
 */
class CompilerDefinition extends ZendCompilerDefinition
{
    /**
     * Returns the injection type
     *
     * @param MethodReflection $method
     * @param ParameterReflection $parameter
     * @return string
     */
    protected function getInjectType(MethodReflection $method, ParameterReflection $parameter, &$required)
    {
        $doc = $method->getDocComment();
        $name = $parameter->getName();
        $pattern = '~@service\s+([a-zA-Z][a-zA-Z0-9.\\\\]*)\s+\$' . $name . '(\s+force)~';
        $m = null;

        if (preg_match($pattern, $doc, $m)) {
            if (isset($m[2]) && !empty($m[2])) {
                $required = true;
            }

            return $m[1];
        }

        if (!$parameter->getClass()) {
            return null;
        }

        return $parameter->getClass()->getName();
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Di\Definition\CompilerDefinition::processParams()
     */
    protected function processParams(&$def, ClassReflection $rClass, MethodReflection $rMethod)
    {
        if (count($rMethod->getParameters()) === 0) {
            return;
        }

        $methodName = $rMethod->getName();
        $def['parameters'][$methodName] = array();

        foreach ($rMethod->getParameters() as $p) {

            /** @var $p \ReflectionParameter  */
            $actualParamName = $p->getName();
            $fqName = $rClass->getName() . '::' . $rMethod->getName() . ':' . $p->getPosition();
            $def['parameters'][$methodName][$fqName] = array();
            $optional = $p->isOptional();
            $required = !$optional;

            // set the class name, if it exists
            $def['parameters'][$methodName][$fqName][] = $actualParamName;
            $def['parameters'][$methodName][$fqName][] = $this->getInjectType($rMethod, $p, $required);
            $def['parameters'][$methodName][$fqName][] = $required;
            $def['parameters'][$methodName][$fqName][] = ($optional)? $p->getDefaultValue() : null;
        }

    }
}
