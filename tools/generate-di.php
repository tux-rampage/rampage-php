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

use rampage\core\di\definition\CompilerDefinition;
use Zend\Code\Scanner\DirectoryScanner as ZendDirectoryScanner;
// use Zend\Code\Scanner\FileScanner;

// Composer?
if (is_readable(__DIR__ . '/../autoload.php')) {
    require_once __DIR__ . '/../autoload.php';
} else {
    set_include_path(
        __DIR__ . '/../library' . PATH_SEPARATOR .
        get_include_path()
    );

    require_once __DIR__ . '/../library/rampage.php';
}

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

$file = $_SERVER['argv'][2]; // __DIR__ . '/../library/rampage/di.compiled.php';
$dir = $_SERVER['argv'][1];

if (!$file || !$dir) {
    echo 'USAGE: ' . basename(__FILE__) . ' <directory> <result-filename>', "\n\n";
    exit(1);
}

$compiler = new CompilerDefinition();
$compiler->setDirectoryScanner(new DirectoryScanner($dir))->compile();

file_put_contents($file, '<?php return ' . var_export($compiler->toArrayDefinition()->toArray(), true) . ';');
echo 'Definition written to: ', $file, "\n";