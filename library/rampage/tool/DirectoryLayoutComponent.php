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
 * Creates the project directory layout
 */
class DirectoryLayoutComponent implements SkeletonComponentInterface
{
    protected $directories = array(
        'application',
        'application/config/conf.d',
        'application/modules',
        'etc',
        'var'
    );

    /**
     * @see \rampage\tool\SkeletonComponentInterface::create()
     */
    public function create(ProjectSkeleton $skeleton)
    {
        $io = $skeleton->getIO();
        $public = $skeleton->getOptions()->getPublicDirectory();

        $this->directories[] = $public;
        $this->directories[] = $public . '/media';

        $io->writeLine('Creating application directories ...');

        foreach ($this->directories as $dir) {
            $io->writeLine("<debug>Creating directory: $dir</debug>", IOInterface::VERBOSITY_VERY);

            if (!$skeleton->createDirectory($dir)) {
                throw new RuntimeException('Failed to create directory: ' . $dir);
            }
        }
    }
}
