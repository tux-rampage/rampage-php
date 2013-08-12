<?php
/**
 * This is part of @application_name@
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
 * @package   @package_name@
 * @author    Axel Helmert
 * @copyright Copyright (c) 2012 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core;

use Zend\Mvc\Application as MvcApplication;
use Zend\Mvc\MvcEvent;

/**
 * Application class
 */
class Application extends MvcApplication
{
    /**
     * Load default config
     *
     * @return array
     */
    protected static function getDefaultconfig()
    {
        return include dirname(__DIR__) . '/default.config.php';
    }

    /**
     * Merge config
     *
     * Merge the given config with the default config
     *
     * @param array $config
     */
    public static function mergeConfig(array $config, array &$result = null)
    {
        if ($result === null) {
            $result = static::getDefaultconfig();
        }

        foreach ($config as $key => $value) {
            if (!is_array($value)) {
                $result[$key] = $value;
                continue;
            }

            if (!isset($result[$key]) || !is_array($result[$key])) {
                $result[$key] = array();
            }

            static::mergeConfig($value, $result[$key]);
        }

        return $result;
    }

    /**
     * Initialize config
     *
     * @param array|string $config
     * @return \rampage\core\Application
     */
    public static function init($config = array())
    {
        self::registerDevelopmentErrorHandler();
        if (!is_array($config)) {
            $config = array();
            $config['service_manager']['path_manager'] = (string)$config;
        }

        $config = static::mergeConfig($config);
        $serviceConfig = isset($config['service_manager']) ? $config['service_manager'] : array();
        $serviceManager = new ServiceManager(new ServiceConfig($serviceConfig));
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();

        return $serviceManager->get('Application')->bootstrap();
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Mvc\Application::bootstrap()
     */
    public function bootstrap()
    {
        $serviceManager = $this->getServiceManager();
        $events = $this->getEventManager();

        $events->attach($serviceManager->get('rampage.ViewInitializer'));
        return parent::bootstrap();
    }

    /**
     * Returns the module manager
     *
     * @return \Zend\ModuleManager\ModuleManagerInterface
     */
    public function getModuleManager()
    {
        return $this->getServiceManager()->get('ModuleManager');
    }

    /**
     * Register development error handler throwing exceptions
     *
     * @param string $force
     */
    public static function registerDevelopmentErrorHandler($force = false)
    {
        if (!$force && (!isset($_SERVER['RAMPAGE_DEVELOPMENT']) || !$_SERVER['RAMPAGE_DEVELOPMENT'])) {
            return;
        }

        set_error_handler(array(__CLASS__, 'errorToException'));
        set_exception_handler(array(__CLASS__, 'handleFinalException'));
    }

    /**
     * Handle final exception
     *
     * @param \Exception $exception
     */
    public static function handleFinalException(\Exception $exception)
    {
        if (PHP_SAPI == 'cli') {
            echo $exception; exit(1);
        }

        @header('Status: 500 Internal Error');
        @header('HTTP/1.1 500 Internal Error');

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $tpl = getcwd() . '/error.php';
        if (is_readable($tpl) && is_file($tpl)) {
            include $tpl;
            exit(1);
        }

        echo '<html><head><title>Application Error</title></head><body style="background: #000; color: #fff;">';

        if (is_readable(__DIR__ . '/failure.jpg')) {
            echo '<img style="float: left;" src="data:image/jpeg;base64,' . base64_encode(file_get_contents(__DIR__ . '/failure.jpg')) . '" />';
        }

        echo '<h1 style="color: #f00;">Application Failure</h1>';
        echo sprintf('<div style="margin-top: 40px;"><strong>Uncaught Exception (%s)</strong>: %s [code: %d]<br /><pre>%s</pre></div>', get_class($exception), $exception->getMessage(), $exception->getCode(), $exception);
        echo '</body></html>';
        exit(1);
    }

    /**
     * Convert php errors to exceptions
     *
     * @throws \RuntimeException
     */
    public static function errorToException($errno, $errstr, $errfile, $errline)
    {
        $exclude = E_STRICT | E_NOTICE | E_USER_NOTICE;
        if (((error_reporting() & $errno) != $errno) || (($exclude & $errno) == $errno)) {
            return false;
        }

        $constants = get_defined_constants(true);
        $name = 'Unknown PHP Error (' . $errno . ')';
        foreach ($constants['Core'] as $c => $value) {
            if ((substr($c, 0, 2) != 'E_') || ($value != $errno)) {
                continue;
            }

            $name = 'PHP ' . ucwords(str_replace('_', ' ', strtolower(substr($c, 2))));
        }

        $exception = new \RuntimeException(sprintf('%s: %s in %s on line %d', $name, $errstr, $errfile, $errline), $errno);

        // Impossible to throw an exception without stack trace
        // happens when errors occour in shutdown scope (i.e. serialize handler)
        if (count($exception->getTrace()) < 1) {
            return false;
        }

        throw $exception;
    }

    /**
     * Complete the request
     *
     * @see \Zend\Mvc\Application::completeRequest()
     */
    protected function completeRequest(MvcEvent $event)
    {
        // Reset the stop propagation flag which could have been set by a dispatch or render listener
        $event->stopPropagation(false);
        return parent::completeRequest($event);
    }
}