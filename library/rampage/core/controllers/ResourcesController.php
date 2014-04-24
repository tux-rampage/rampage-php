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

namespace rampage\core\controllers;

use rampage\core\resources\PublishingStrategyInterface;
use rampage\core\resources\ThemeInterface;
use rampage\core\exception\RuntimeException;
use rampage\core\Logger;
use rampage\core\ConsoleLogWriter;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Log\LoggerAwareInterface;
use Zend\Console\Console;

use Zend\Http\Request as HttpRequest;
use Zend\Http\Response\Stream as StreamResponse;
use Zend\Http\Header\LastModified;
use Zend\Http\Header\IfModifiedSince;
use Zend\Http\Header\Expires;

use SplFileInfo;
use DateTime;
use Zend\Http\Header\IfNoneMatch;

/**
 * Resources controller
 *
 * @method HttpRequest getRequest()
 * @method \Zend\Http\Response getResponse()
 */
class ResourcesController extends AbstractActionController
{
    /**
     * @var array
     */
    protected $mimes = array(
        'c' => 'text/plain',
        'cc' => 'text/plain',
        'cpp' => 'text/plain',
        'c++' => 'text/plain',
        'dtd' => 'text/plain',
        'h' => 'text/plain',
        'log' => 'text/plain',
        'rng' => 'text/plain',
        'txt' => 'text/plain',
        'xsd' => 'text/plain',
        'avi' => 'video/avi',
        'bmp' => 'image/bmp',
        'css' => 'text/css',
        'gif' => 'image/gif',
        'htm' => 'text/html',
        'html' => 'text/html',
        'htmls' => 'text/html',
        'ico' => 'image/x-ico',
        'jpe' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'js' => 'application/x-javascript',
        'midi' => 'audio/midi',
        'mid' => 'audio/midi',
        'mod' => 'audio/mod',
        'mov' => 'movie/quicktime',
        'mp3' => 'audio/mp3',
        'mpg' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'swf' => 'application/shockwave-flash',
        'svg' => 'image/svg+xml',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'wav' => 'audio/wav',
        'xbm' => 'image/xbm',
        'xml' => 'text/xml',
    );

    /**
     * @param string $extension
     * @param string $mimetype
     * @return self
     */
    public function addExtension($extension, $mimetype)
    {
        $this->mimes[$extension] = $mimetype;
        return $this;
    }

    /**
     * @param SplFileInfo $info
     */
    protected function findMimeType(SplFileInfo $info)
    {
        $ext = $info->getExtension();
        if (!isset($this->mimes[$ext])) {
            return 'application/octet-stream';
        }

        return $this->mimes[$ext];
    }

    /**
     * @param string $path
     * @return string
     */
    protected function normalizePath($path)
    {
        $path = trim($path, '/');
        $segments = explode('/', $path);
        $normalized = array();

        foreach ($segments as $segment) {
            if (in_array($segment, array('.', ''))) {
                continue;
            }

            if ($segment == '..') {
                array_pop($normalized);
                continue;
            }

            $normalized[] = $segment;
        }

        return implode('/', $normalized);
    }

    /**
     * @param \SplFileInfo $info
     * @return string
     */
    protected function getETag(\SplFileInfo $info)
    {
        return md5($info->getMTime() . ':' . $info->getPathname());
    }

    /**
     * @param \SplFileInfo $info
     * @return boolean
     */
    protected function isCachedByBrowser(SplFileInfo $info)
    {
        if (!$this->getRequest() instanceof HttpRequest) {
            return false;
        }

        $ifModified = $this->getRequest()->getHeader('If-Modified-Since');
        $ifNoneMatch = $this->getRequest()->getHeader('If-None-Match');
        $pragma = $this->getRequest()->getHeader('Pragma');

        // Client requests the resource uncached
        if ($pragma && ($pragma->getFieldValue() == 'no-cache')) {
            return false;
        }

        // Check if ETag is present and matches
        if (($ifNoneMatch instanceof IfNoneMatch) && ($ifNoneMatch->getFieldValue() == $this->getETag($info))) {
            return true;
        }

        // Check if modified since is present and matches
        if (!$ifModified instanceof IfModifiedSince) {
            return false;
        }

        $isModified = ($info->getMTime() > $ifModified->date()->getTimestamp());
        return !$isModified;
    }

    /**
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        if (!$this->getRequest() instanceof HttpRequest) {
            throw new \BadMethodCallException('This action is only available in HTTP context.');
        }

        $themeName = $this->params('theme');
        $scope = $this->params('scope');
        $file = $this->params('file');
        $file = $this->normalizePath($file);

        // validate path
        if (($themeName == '..') || ($scope == '..') || !$file) {
            return $this->notFoundAction();
        }

        // Get the theme service
        $theme = $this->getServiceLocator()->get('rampage.Theme');
        if (!$theme instanceof ThemeInterface) {
            return $this->notFoundAction();
        }

        if ($scope == '__theme__') {
            $scope = false;
        }

        // Resolve the file from theme
        $theme->setCurrentTheme($themeName);
        $info = $theme->resolve('public', $file, $scope, true);

        if (($info === false) || !$info->isFile() || !$info->isReadable()) {
            return $this->notFoundAction();
        }

        // Check for browser cache
        if ($this->isCachedByBrowser($info)) {
            return $this->getResponse()->setStatusCode(304);
        }

        $eTag = $this->getETag($info);
        $modified = new DateTime();
        $lastMod = new LastModified();
        $expires = new Expires();
        $maxAge = 60 * 60 * 24; // 1day

        $modified->setTimestamp($info->getMTime());
        $lastMod->setDate($modified);
        $expires->date()->modify('+1 day');

        $response = new StreamResponse();
        $response->getHeaders()
            ->addHeader($lastMod)
            ->addHeader($expires)
            ->addHeaderLine('Cache-Control', 'public, max-age=' . $maxAge)
            ->addHeaderLine('ETag', $eTag)
            ->addHeaderLine('Content-Type', $this->findMimeType($info));

        $response->setStream(fopen($info->getPathname(), 'r'));
        return $response;
    }

    /**
     * @throws RuntimeException
     * @throws \BadMethodCallException
     */
    public function publishAction()
    {
        $strategy = $this->getServiceLocator()->get('rampage.ResourcePublishingStrategy');
        if (!$strategy instanceof PublishingStrategyInterface) {
            throw new RuntimeException('No resource publishing strategy available!');
        }

        if (!Console::isConsole()) {
            throw new \BadMethodCallException('This action is available for console, only.');
        }

        if ($strategy instanceof LoggerAwareInterface) {
            $logger = new Logger();
            $writer = new ConsoleLogWriter();

            $writer->setConsoleAdapter($this->getServiceLocator()->get('console'));
            $logger->addWriter($writer);

            $strategy->setLogger($logger);
        }

        $strategy->publish();
    }
}
