<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\tool;

use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\Console\ColorInterface;
use Zend\Console\Request as ConsoleRequest;

use Zend\Mvc\View\Console\RouteNotFoundStrategy;
use Zend\Mvc\Exception as mvcexceptions;

use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;


class ConsoleHelpRenderer extends RouteNotFoundStrategy
{
    /**
     * {@inheritdoc}
     * @see \Zend\Mvc\View\Console\RouteNotFoundStrategy::getConsoleUsage()
     */
    protected function getConsoleUsage(ConsoleAdapterInterface $console, $scriptName, ModuleManagerInterface $moduleManager = null)
    {
        /*
         * Loop through all loaded modules and collect usage info
        */
        $usageInfo = array();

        if ($moduleManager !== null) {
            foreach ($moduleManager->getLoadedModules(false) as $name => $module) {
                // Strict-type on ConsoleUsageProviderInterface, or duck-type
                // on the method it defines
                if (!$module instanceof ConsoleUsageProviderInterface && !method_exists($module, 'getConsoleUsage')) {
                    continue; // this module does not provide usage info
                }

                $moduleName = (method_exists($module, 'getConsoleLabel'))? $module->getConsoleLabel() : $name;

                if ($moduleName) {
                    // We prepend the usage by the module name (printed in red), so that each module is
                    // clearly visible by the user
                    $moduleName = sprintf("%s\n%s\n%s\n",
                        str_repeat('-', $console->getWidth()),
                        $moduleName,
                        str_repeat('-', $console->getWidth())
                    );

                    $moduleName = $console->colorize($moduleName, ColorInterface::RED);
                }

                $usage = $module->getConsoleUsage($console);

                if (is_array($usage) && !empty($usage)) {
                    $usageInfo[$name] = $usage;
                } elseif (is_string($usage) && ($usage != '')) {
                    $usageInfo[$name] = array($usage);
                }

                if ($moduleName && isset($usageInfo[$name])) {
                    array_unshift($usageInfo[$name], $moduleName);
                }
            }
        }

        /*
         * Handle an application with no usage information
        */
        if (!count($usageInfo)) {
            return '';
        }

        /*
         * Transform arrays in usage info into columns, otherwise join everything together
        */
        $result    = '';
        $table     = false;
        $tableCols = 0;
        $tableType = 0;
        foreach ($usageInfo as $moduleName => $usage) {
            if (!is_string($usage) && !is_array($usage)) {
                throw new mvcexceptions\RuntimeException(sprintf('Cannot understand usage info for module "%s"', $moduleName));
            }

            if (is_string($usage)) {
                // It's a plain string - output as is
                $result .= $usage . "\n";
                continue;
            }

            // It's an array, analyze it
            foreach ($usage as $a => $b) {
                /*
                 * 'invocation method' => 'explanation'
                */
                if (is_string($a) && is_string($b)) {
                    if (($tableCols !== 2 || $tableType != 1) && $table !== false) {
                        // render last table
                        $result .= $this->renderTable($table, $tableCols, $console->getWidth());
                        $table   = false;

                        // add extra newline for clarity
                        $result .= "\n";
                    }

                    // Colorize the command
                    $a = $console->colorize($scriptName . ' ' . $a, ColorInterface::GREEN);

                    $tableCols = 2;
                    $tableType = 1;
                    $table[]   = array($a, $b);
                    continue;
                }

                /*
                 * array('--param', '--explanation')
                */
                if (is_array($b)) {
                    if ((count($b) != $tableCols || $tableType != 2) && $table !== false) {
                        // render last table
                        $result .= $this->renderTable($table, $tableCols, $console->getWidth());
                        $table   = false;

                        // add extra newline for clarity
                        $result .= "\n";
                    }

                    $tableCols = count($b);
                    $tableType = 2;
                    $table[]   = $b;
                    continue;
                }

                /*
                 * 'A single line of text'
                */
                if ($table !== false) {
                    // render last table
                    $result .= $this->renderTable($table, $tableCols, $console->getWidth());
                    $table   = false;

                    // add extra newline for clarity
                    $result .= "\n";
                }

                $tableType = 0;
                $result   .= $b . "\n";
            }
        }

        // Finish last table
        if ($table !== false) {
            $result .= $this->renderTable($table, $tableCols, $console->getWidth());
        }

        return $result;
    }

    /**
     * @return string
     */
    public function renderHelp(ConsoleAdapterInterface $console, ModuleManagerInterface $moduleManager, $scriptName = null)
    {
        if (!$scriptName) {
            $request = new ConsoleRequest();
            $scriptName = basename($request->getScriptName());
        }

        // Get application banner
        $banner = $this->getConsoleBanner($console, $moduleManager);

        // Get application usage information
        $usage = $this->getConsoleUsage($console, $scriptName, $moduleManager);

        // Inject the text into view
        $result  = $banner ? rtrim($banner, "\n")        : '';
        $result .= $usage  ? "\n\n" . trim($usage, "\n") : '';
        $result .= "\n\n"; // to ensure we output a final newline

        return $result;
    }
}
