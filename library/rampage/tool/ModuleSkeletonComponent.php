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
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;

class ModuleSkeletonComponent implements SkeletonComponentInterface
{
    /**
     * @var ProjectSkeleton
     */
    protected $skeleton = null;

    /**
     * @var array
     */
    protected $directories = array(
        'resource/layout',
        'resource/public',
        'resource/template',
        'src',
    );

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->skeleton->getOptions()->get('module-name', 'application');
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return str_replace('.', '\\', $this->getModuleName());
    }

    /**
     * @param string $file
     * @return string
     */
    protected function getRelativePath($file)
    {
        $path = 'application/modules/' . $this->getModuleName() . '/' . $file;
        return $path;
    }

    /**
     * @param string $file
     * @return string
     */
    protected function getFilepath($file)
    {
        $path = $this->skeleton->getDirectory() . $this->getRelativePath($file);
        return $path;
    }

    /**
     * @return string
     */
    protected function getFileHeader()
    {
        return (string)$this->skeleton->getOptions()->get('php-header');
    }

    /**
     * @param string $file
     * @param string $content
     * @return self
     */
    protected function writeContent($file, $content)
    {
        $path = $this->getFilepath($file);
        if (file_exists($path)) {
            trigger_error('File already exists: ' . $file, E_USER_WARNING);
            return $this;
        }

        if (file_put_contents($path, $content) === false) {
            throw new RuntimeException('Failed to write file: ' . $file);
        }

        return $this;
    }

    /**
     * @return self
     */
    public function createModulePhp()
    {
        $generator = new ClassGenerator();
        $generator->setNamespaceName($this->getNamespace())
            ->setName('Module')
            ->addUse('rampage\core\AbstractModule')
            ->addUse('rampage\core\ModuleManifest')
            ->addUse('Zend\ModuleManager\Feature\ConfigProviderInterface');

        $construct = new MethodGenerator();
        $construct->setName('__construct')
            ->setVisibility(MethodGenerator::VISIBILITY_PUBLIC)
            ->setSourceContent('parent::__construct(new ModuleManifest(__DIR__, __DIR__ . \'/module.xml\'));');

        $getConfig = new MethodGenerator();
        $getConfig->setName('getConfig')
            ->setVisibility(MethodGenerator::VISIBILITY_PUBLIC)
            ->setSourceContent('return $this->fetchConfigArray(__DIR__ . \'/etc/conf/config.php\');');

        $generator->addMethodFromGenerator($construct)
            ->addMethodFromGenerator($getConfig);

        $this->writeContent('Module.php', $generator->generate());
        return $this;
    }

    /**
     * @return self
     */
    public function createManifestXml()
    {
        $xml = <<<__XML__
<?xml version="1.0" encoding="UTF-8"?>
<manifest xmlns="http://www.linux-rampage.org/ModuleManifest" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.linux-rampage.org/ModuleManifest http://www.linux-rampage.org/ModuleManifest ">
    <module name="{$this->getModuleName()}" version="1.0.0">
    </module>

    <!-- This might be optional for example when you're using composer autoload dumps -->
    <!--
    <classes>
        <namespaces>
            <namespace namespace="{$this->getNamespace()}" path="src" />
        </namespaces>
    </classes>
    -->

    <router>
        <route name="root" type="literal">
            <literal route="/" controller="index" action="index">
            </literal>
        </route>
    </router>

    <!-- This is for convenience, you might also refer to a controller with its full class name -->
    <plugins>
        <pluginmanager type="controllers">
            <services>
                <service name="index" class="luka.lissy.core.controllers.IndexController" />
            </services>
        </pluginmanager>
    </plugins>

    <!-- You may define your services here -->
    <!--
    <servicemanager>
        <services>
            <service name="Zend\\Authentication\\AuthenticationService">
                <factory class="{$this->getNamespace()}\\services\\AuthServiceFactory" />
                <aliases>
                    <alias name="AuthService" />
                </aliases>
            </service>
        </services>
    </servicemanager>
    -->

    <resources>
        <layout>
            <config scope="{$this->getModuleName()}" file="layout.xml" />
        </layout>

        <paths>
            <path scope="{$this->getModuleName()}" path="resource" />
        </paths>
    </resources>
</manifest>

__XML__;

        $this->writeContent('module.xml', $xml);

        return $this;
    }

    /**
     * @see \rampage\tool\SkeletonComponentInterface::create()
     */
    public function create(ProjectSkeleton $skeleton)
    {
        $this->skeleton = $skeleton;

        foreach ($this->directories as $dir) {
            if (!$skeleton->createDirectory($this->getRelativePath($dir))) {
                throw new RuntimeException('Could not create module directory: ' . $dir);
            }
        }

        $this->createManifestXml()
            ->createModulePhp();

        return $this;
    }
}
