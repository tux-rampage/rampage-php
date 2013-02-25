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

namespace rampagetools;

set_include_path(
    __DIR__ . '/../library' . PATH_SEPARATOR .
    get_include_path()
);

require_once __DIR__ . '/../library/rampage.php';
use Zend\Di\Definition\CompilerDefinition as ZendCompilerDefinition;
use Zend\Code\Scanner\DirectoryScanner as ZendDirectoryScanner;
use Zend\Di\Definition\IntrospectionStrategy;
// use Zend\Code\Scanner\FileScanner;
use Zend\Code\Reflection\MethodReflection;
use ReflectionParameter as ParameterReflection;

/**
 * Directory scanner
 */
class DirectoryScanner extends ZendDirectoryScanner
{
	/**
     * (non-PHPdoc)
     * @see \Zend\Code\Scanner\DirectoryScanner::getClassNames()
     */
    public function getClassNames()
    {
        $classes = parent::getClassNames();
        $classes = array_filter($classes, function($class) {
            if ((substr($class, -9) == 'Exception') || (strpos($class, '\\exception\\') !== false)) {
                return false;
            }

            if (strpos($class, 'rampage\core\xml') === 0) {
                return false;
            }

            if ((strpos($class, 'rampage\test') === 0)
                || (strpos($class, 'rampage\phing') === 0)
                || (strpos($class, 'rampage\core\di') === 0)) {
                return false;
            }


            return true;
        });

        return $classes;
    }
}

/*
 * Compiler Def
 */
class CompilerDefinition extends ZendCompilerDefinition
{
	/**
     * (non-PHPdoc)
     * @see \Zend\Di\Definition\CompilerDefinition::__construct()
     */
    public function __construct(IntrospectionStrategy $introspectionStrategy = null)
    {
        parent::__construct($introspectionStrategy);
        $this->directoryScanner = new DirectoryScanner(__DIR__ . '/../library/');
        //$this->addCodeScannerFile(new FileScanner(__DIR__ . '/../library/rampage/core/resource/Theme.php'));
    }

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
    protected function processParams(&$def, \Zend\Code\Reflection\ClassReflection $rClass, \Zend\Code\Reflection\MethodReflection $rMethod)
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
            $def['parameters'][$methodName][$fqName][] = $this->getInjectType($rMethod, $p, $required); //($p->getClass() !== null) ? $p->getClass()->getName() : null;
            $def['parameters'][$methodName][$fqName][] = $required;
            $def['parameters'][$methodName][$fqName][] = ($optional)? $p->getDefaultValue() : null;
        }

    }
}

$file = __DIR__ . '/../library/rampage/di.compiled.php';
$compiler = new CompilerDefinition();
$compiler->compile();

file_put_contents($file, '<?php return ' . var_export($compiler->toArrayDefinition()->toArray(), true) . ';');

echo 'Definition written to: ', $file, "\n";