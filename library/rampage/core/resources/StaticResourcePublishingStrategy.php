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

namespace rampage\core\resources;

use rampage\core\exception;
use rampage\core\PathManager;
use rampage\core\url\UrlModelLocator;

use DirectoryIterator;
use SplFileInfo;

use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerInterface;
use rampage\io\IOInterface;
use rampage\io\NullIO;

/**
 * Static resource publishing strategy
 */
class StaticResourcePublishingStrategy implements PublishingStrategyInterface, LoggerAwareInterface
{
    const SCOPE_THEME = 'theme';
    const SCOPE_RESOURCE = 'resource';

    /**
     * @var string
     */
    private $targetDir = null;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var UrlModelLocator
     */
    protected $urlManager = null;

    /**
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * @var IOInterface
     */
    protected $io = null;

    /**
     * @param ModuleRegistry $moduleRegistry
     * @param PathManager $pathManager
     */
    public function __construct($targetDir, array $config = array(), IOInterface $io = null)
    {
        $this->targetDir = $targetDir;
        $this->config = $config;
        $this->io = $io? : new NullIO();
    }

    /**
     * @see \Zend\Log\LoggerAwareInterface::setLogger()
     * @return self
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param UrlModelLocator $urlManager
     * @return self
     */
    public function setUrlManager(UrlModelLocator $urlManager)
    {
        $this->urlManager = $urlManager;
        return $this;
    }

    /**
     * @return \rampage\core\url\UrlModelInterface
     */
    protected function getUrlModel()
    {
        if ($this->urlManager && $this->urlManager->has('static')) {
            return $this->urlManager->get('static');
        }

        return false;
    }

    /**
     * @param string|array $pathConfig
     * @return string|boolean
     */
    protected function getPublicPath($pathConfig)
    {
        if (!is_array($pathConfig)) {
            return $pathConfig . '/public';
        }

        if (isset($pathConfig['public'])) {
            return $pathConfig['public'];
        }

        if (!isset($pathConfig['base'])) {
            return false;
        }

        return $pathConfig['base'] . '/public';
    }

    /**
     * @param string $source
     * @param string $target
     */
    protected function copyDir($source, $target, $filter = null)
    {
        $targetInfo = new SplFileInfo($target);
        if (!$targetInfo->isDir() && !mkdir($target, 0777, true)) {
            throw new exception\RuntimeException('Filed to create directory: ' . $target);
        }

        /* @var $file \SplFileInfo */
        $iterator = new DirectoryIterator($source);
        foreach ($iterator as $file) {
            if (in_array($file->getFilename(), array('.', '..', '.svn', '.git'))) {
                continue;
            }

            if (is_callable($filter) && !$filter($file)) {
                continue;
            }

            if ($file->isDir()) {
                $this->copyDir($file->getPathname(), $target . '/' . $file->getFilename(), $filter);
                continue;
            }

            $this->log('Publishing file: <info>' . $target . '/' . $file->getFilename() . '</info> ...');
            copy($file->getPathname(), $target . '/' . $file->getFilename());
        }

        return $this;
    }

    /**
     * @param array $extensions
     * @return NULL|Closure
     */
    protected function getExtensionFilter(array $extensions)
    {
        if (empty($extensions)) {
            return null;
        }

        return function(SplFileInfo $info) use ($extensions) {
            if ($info->isDir()) {
                return true;
            }

            foreach ($extensions as $ext) {
                if ($ext == '') {
                    continue;
                }

                if (substr($info->getFilename(), 0 - strlen($ext)) == $ext) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * @todo use logger
     * @param string $message
     * @return self
     */
    protected function log($message)
    {
        $this->io->writeLine($message);

        if ($this->logger) {
            $this->logger->info($message);
        }

        return $this;
    }

    /**
     * @param string $targetDir
     * @param array $extensions
     */
    protected function publishResources($targetDir, array $extensions)
    {
        if (!isset($this->config['rampage']['resources'])) {
            return $this;
        }

        $filter = $this->getExtensionFilter($extensions);
        foreach ($this->config['rampage']['resources'] as $scope => $paths) {
            $this->log('Publishing resource files for <info>' . $scope . '</info> ...');

            $sourceDir = $this->getPublicPath($paths);
            if (!$sourceDir) {
                continue;
            }

            $segments = array($targetDir, self::SCOPE_RESOURCE, $scope);
            $path = implode('/', array_filter($segments));

            $this->copyDir($sourceDir, $path, $filter);
        }

        return $this;
    }

    /**
     * @param string $targetDir
     * @param array $extensions
     */
    protected function publishThemeFiles($targetDir, array $extensions)
    {
        if (!isset($this->config['rampage']['themes'])) {
            return $this;
        }

        $filter = $this->getExtensionFilter($extensions);

        foreach ($this->config['rampage']['themes'] as $name => $themeConfig) {
            $this->log('Publishing theme files for <info>' . $name . '</info> ...');
            if (!isset($themeConfig['path']) || !($sourceDir = $this->getPublicPath($themeConfig['path']))) {
                $this->log(sprintf('<warning>No usable path config for theme "%s"!</warning>', $themeConfig));
                continue;
            }

            $segments = array($targetDir, self::SCOPE_THEME, $name);
            $path = implode('/', array_filter($segments));

            $this->copyDir($sourceDir, $path, $filter);
        }

        return $this;
    }

    /**
     * @param array $segments
     * @return string
     */
    protected function findStaticFile(array $segments)
    {
        $path = implode('/', array_filter($segments));
        $info = new SplFileInfo($this->targetDir . '/' . $path);

        if ($info->isFile()) {
            return $path;
        }

        return false;
    }

    /**
     * @param string $file
     * @param string $scope
     * @param ThemeInterface $theme
     */
    public function find($file, $scope, ThemeInterface $theme)
    {
        $result = false;
        $themes = $theme->getFallbackThemes();

        array_unshift($themes, $theme->getCurrentTheme());

        foreach ($themes as $themeName) {
            $result = $this->findStaticFile(array(self::SCOPE_THEME, $themeName, $scope, $file));

            if ($result) {
                break;
            }
        }

        if (($result === false) && $scope) {
            $result = $this->findStaticFile(array(self::SCOPE_RESOURCE, $scope, $file));
        }

        if (($result !== false) && ($urlModel = $this->getUrlModel())) {
            $result = $urlModel->getUrl($result);
        }

        return $result;
    }

    /**
     * Publish all module resources
     *
     * @param string $targetDir
     * @param array $extensions
     */
    public function publish($targetDir = null, array $extensions = array())
    {
        if (!$targetDir) {
            $targetDir = $this->targetDir;
        }

        $this->publishResources($targetDir, $extensions);
        $this->publishThemeFiles($targetDir, $extensions);

        return $this;
    }

}
