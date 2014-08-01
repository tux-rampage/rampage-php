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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\tool;

use RuntimeException;
use rampage\io\IOInterface;

/**
 * Bootstrap generator
 */
class BootstrapGeneratorComponent implements SkeletonComponentInterface
{
    /**
     * @var ProjectSkeleton
     */
    protected $skeleton = null;

    /**
     * @var IOInterface
     */
    protected $io = null;

    /**
     * @return string
     */
    protected function getFileHeader()
    {
        return (string)$this->skeleton->getOptions()->get('php-header');
    }

    /**
     * @param string $file
     * @return string
     */
    protected function getFilepath($file)
    {
        $path = $this->skeleton->getDirectory() . $file;
        return $path;
    }

    /**
     * @param string $file
     * @return string
     */
    protected function getPublicFilepath($file)
    {
        $dir = $this->skeleton->getOptions()->getPublicDirectory();
        $path = $this->skeleton->getDirectory() . $dir . '/' . $file;

        return $path;
    }

    /**
     * @param string $file
     * @param string $content
     * @param bool $public
     */
    protected function writeContent($file, $content, $public = false)
    {
        $path = ($public)? $this->getPublicFilepath($file) : $this->getFilepath($file);
        if (file_exists($path)) {
            $this->io->writeLine('<warning>Skip: ' . $file . ' - File already exists</warning>');
            return $this;
        }

        if (file_put_contents($path, $content) === false) {
            throw new RuntimeException('Failed to write file: ' . $file);
        }

        return $this;
    }

    /**
     * Create the index document
     *
     * @return self
     */
    protected function createIndexPhp()
    {
        $content = <<<__EOF__
<?php
{$this->getFileHeader()}
// OPTIONAL: Change the directory one level up
chdir(dirname(__DIR__));

require_once __DIR__ . '/../application/bootstrap.php';
rampage\core\Application::init(include APPLICATION_DIR . 'config/application.conf.php')->run();

__EOF__;

        $this->writeContent('index.php', $content, true);
        return $this;
    }

    /**
     * @return self
     */
    protected function createBootstrapPhp()
    {
        $content = <<<__EOF__
<?php
{$this->getFileHeader()}

define('APPLICATION_DIR', __DIR__ . '/');
define('APPLICATION_DEVELOPMENT', (isset(\$_SERVER['APPLICATION_DEVELOPMENT']) && \$_SERVER['APPLICATION_DEVELOPMENT']));

require_once __DIR__ . '/../vendor/autoload.php';

// Register the final exception handler
rampage\core\Application::registerExceptionHandler(true);

// Register the error to exception handler
//rampage\core\Application::registerDevelopmentErrorHandler(true);

__EOF__;

        $this->writeContent('application/bootstrap.php', $content);
        return $this;
    }

    /**
     * @return self
     */
    protected function createAppConfig()
    {
        $content = <<<__EOF__
<?php
// This is the default ZF2 config
// Refer to http://framework.zend.com/ for further information
return array(
    // Fetch modules defintion
    'modules' => require __DIR__ . '/modules.conf.php',

    // Define additional pathmanager locations
    //'path_manager' => array(
    //    'app' => dirname(__DIR__),
    //),

    // These are various options for the listeners attached to the ModuleManager
    'module_listener_options' => array(
        // An array of paths from which to glob configuration files after
        // modules are loaded. These effectively override configuration
        // provided by modules themselves. Paths may use GLOB_BRACE notation.
        'config_glob_paths' => array(
            __DIR__ . '/conf.d/{,*.}{global,local}.php',
        ),
    )
);

__EOF__;

        $export = function($value) {
            return var_export($value, true);
        };

        $modules = array($this->skeleton->getOptions()->get('main-module-name', 'application'));
        $modulesFormat = "<?php return array(%s);\n";
        $modules = implode(', ', array_map($export, $modules));

        $this->writeContent('application/config/application.conf.php', $content)
            ->writeContent('application/config/modules.conf.php', sprintf($modulesFormat, $modules));

        return $this;
    }

    /**
     * @return self
     */
    protected function createMainModule()
    {
        $this->io->writeLine('Creating main application module ...');

        $module = new ModuleSkeletonComponent($this->skeleton->getOptions()->get('main-module-name', 'application'));
        $module->create($this->skeleton);

        return $this;
    }

    /**
     * @see \rampage\tool\SkeletonComponentInterface::create()
     */
    public function create(ProjectSkeleton $skeleton)
    {
        $this->io = $skeleton->getIO();
        $this->skeleton = $skeleton;

        $this->io->writeLine('Creating application bootstrap files ...');

        $this->createAppConfig()
            ->createBootstrapPhp()
            ->createIndexPhp()
            ->createMainModule();

        return $this;
    }
}
