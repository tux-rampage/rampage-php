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
 * @package   rampage.phing
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\phing\tasks;

// Phing
require_once 'phing/Task.php';
use Task;
use Project;
use FileSet;
use PhingFile;
use BuildException;

// Libs
use rampage\core\di\definition\CompilerDefinition;
use Zend\Code\Scanner\FileScanner;

/**
 * Di compiler task
 */
class DiCompilerTask extends Task
{
    /**
     * Filesets
     * @var array
     */
    protected $filesets = array();

    /**
     * Definition file
     * @var \PhingFile
     */
    protected $definitionFile = array();

    /**
     * Create a new fileset
     *
     * @return \FileSet
     */
    public function createFileSet()
    {
        $fileset = new FileSet();
        $this->filesets[] = $fileset;

        return $fileset;
    }

    /**
     * Set the definition file to write to
     *
     * @param PhingFile $file
     */
    public function setDefinitionFile(PhingFile $file)
    {
        $this->definitionFile = $file;
    }

    /**
     * Synonym for setDefinitionFile
     *
     * @param PhingFile $file
     */
    public function setTarget(PhingFile $file)
    {
        $this->setDefinitionFile($file);
    }

    /**
     * (non-PHPdoc)
     * @see Task::main()
     */
    public function main()
    {
        $compiler = new CompilerDefinition();
        $target = $this->definitionFile->getAbsolutePath();

        /* @var $fileset \FileSet */
        foreach ($this->filesets as $fileset) {
            $scanner = $fileset->getDirectoryScanner($this->getProject());
            $dir = $scanner->getBasedir();

            foreach ($scanner->getIncludedFiles() as $file) {
                $compiler->addCodeScannerFile(new FileScanner($dir . '/' . $file));
            }
        }

        $compiler->compile();
        $definition = '<?php return ' . var_export($compiler->toArrayDefinition()->toArray(), true) . ';';
        if (!file_put_contents($target, $definition)) {
            throw new BuildException('Failed to write definition to "' . $target . '"');
        }

        $this->log('Wrote definition to "' . $target . '"', Project::MSG_INFO);
    }
}
