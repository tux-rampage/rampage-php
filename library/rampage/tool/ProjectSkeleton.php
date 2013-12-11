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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\tool;

class ProjectSkeleton
{
    /**
     * @var string
     */
    protected $directory = null;

    /**
     * @var SkeletonComponentInterface[]
     */
    protected $components = array();

    /**
     * @var OptionsContainer
     */
    protected $options = null;

    /**
     * @param string $directory
     */
    public function __construct($directory = null)
    {
        $this->options = new OptionsContainer();

        $this->setDirectory($directory? : getcwd());
        $this->addComponent(new DirectoryLayoutComponent())
            ->addComponent(new BootstrapGeneratorComponent())
            ->addComponent(new ModuleSkeletonComponent());
    }

    /**
     * @param string $dir
     * @return self
     */
    public function setDirectory($dir)
    {
        if (substr($dir, -1) !== '/') {
            $dir .= '/';
        }

        $this->directory = $dir;
        return $this;
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param array|OptionsContainer $options
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setOptions($options)
    {
        if (is_array($options)) {
            $this->options->exchangeArray($options);
            return $this;
        }

        if (!$options instanceof OptionsContainer) {
            throw new \InvalidArgumentException('Invalid type for $options: ' . (is_object($options)? get_class($options) : gettype($options)));
        }

        $this->options = $options;
        return $this;
    }

    /**
     * @return \rampage\tool\OptionsContainer
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param SkeletonComponentInterface $component
     * @return self
     */
    public function addComponent(SkeletonComponentInterface $component)
    {
        $this->components[] = $component;
        return $this;
    }

    /**
     * @param string $dir
     * @return boolean
     */
    public function createDirectory($dir)
    {
        $path = $this->directory . '/' . $dir;

        if (is_dir($path)) {
            return true;
        }

        return mkdir($path, 0775, true);
    }

    /**
     * @param string $options
     * @return self
     */
    public function create($options = null)
    {
        if ($options !== null) {
            $this->setOptions($options);
        }

        foreach ($this->components as $component) {
            $component->create($this);
        }

        return $this;
    }
}
