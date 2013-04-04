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
 * @package   rampage.composer
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\IO\IOInterface;
use Composer\Composer;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * Module installer
 */
class ComposerInstaller extends LibraryInstaller
{
    /**
     * Module dir
     *
     * @var string
     */
    protected $moduleDir = 'modules';

    /**
     * Modules config
     *
     * @var string
     */
    protected $modulesConfig = 'application/etc/modules.conf';

	/**
     * @see \Composer\Installer\LibraryInstaller::__construct()
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'library')
    {
        parent::__construct($io, $composer, $type);

        if ($modulesDir = $composer->getConfig()->get('rampage.modules-dir')) {
            $this->moduleDir = $modulesDir;
        }

        if ($modConfig = $composer->getConfig()->get('rampage.modules-config')) {
            $this->modulesConfig = $modConfig;
        }
    }

    /**
     * Get the module name
     *
     * @param PackageInterface $package
     * @return string
     */
    protected function getModuleName(PackageInterface $package)
    {
        $extra = $package->getExtra();

        if (isset($extra['modulename'])) {
            $name = $extra['modulename'];
        } else {
            $name = strtr($package->getPrettyName(), array(
                '\\' => '.',
                '-' => '.',
                '_' => '.',
                ' ' => ''
            ));
        }

        return $name;
    }

	/**
     * @see \Composer\Installer\InstallerInterface::getInstallPath()
     */
    public function getInstallPath(PackageInterface $package)
    {
        return rtrim($this->moduleDir, '/') . '/' . $this->getModuleName($package);
    }

    /**
     * @see \Composer\Installer\LibraryInstaller::install()
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);

        $conf = @parse_ini_file($this->modulesConfig);
        if (!is_array($conf)) {
            $conf = array();
        }

        $conf[$this->getModuleName($package)] = true;
    }

	/**
     * @see \Composer\Installer\InstallerInterface::supports()
     */
    public function supports($packageType)
    {
        return ($packageType == 'rampage-module');
    }
}