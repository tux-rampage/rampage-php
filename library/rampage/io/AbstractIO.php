<?php
/**
 * Copyright (c) 2014 Axel Helmert
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
 * @author    Axel Helmert
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\io;


abstract class AbstractIO implements IOInterface
{
    /**
     * @var int
     */
    protected $verbosity = self::VERBOSITY_NORMAL;

    /**
     * {@inheritdoc}
     * @see \rampage\tool\IOInterface::getVerbosity()
     */
    public function getVerbosity()
    {
        return $this->verbosity;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\tool\IOInterface::isDebug()
     */
    public function isDebug()
    {
        return ($this->verbosity >= self::VERBOSITY_DEBUG);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\tool\IOInterface::isSilent()
     */
    public function isSilent()
    {
        return $this->verbosity <= self::VERBOSITY_SILENT;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\tool\IOInterface::isVerbose()
     */
    public function isVerbose()
    {
        return ($this->verbosity >= self::VERBOSITY_VERBOSE);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\tool\IOInterface::isVeryVerbose()
     */
    public function isVeryVerbose()
    {
        return ($this->verbosity >= self::VERBOSITY_VERY);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\tool\IOInterface::setVerbosity()
     */
    public function setVerbosity($verbosity)
    {
        $this->verbosity = (int)$verbosity;
    }
}
