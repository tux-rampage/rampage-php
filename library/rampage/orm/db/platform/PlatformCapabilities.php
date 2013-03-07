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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\db\platform;

use IteratorAggregate;
use ArrayIterator;

/**
 * Platform capabilities
 */
class PlatformCapabilities implements IteratorAggregate
{
    /**
     * Automatic identity columns
     */
    const AUTO_IDENTITY = 'autoidentity';

    /**
     * Capabilities
     *
     * @var array
     */
    private $capabilities = array();

    /**
     * Construct
     *
     * @return string
     */
    public function __construct(array $capabilities = array())
    {
        foreach ($capabilities as $key => $capability) {
            if (is_string($key)) {
                $this->capabilities[$key] = $capability;
                continue;
            }

            $this->capabilities[$capability] = true;
        }
    }

    /**
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new ArrayIterator($this->capabilities);
    }

    /**
     * Check for capability
     *
     * @param string $capability
     * @return bool
     */
	public function has($capability)
    {
        if (isset($this->capabilities[$capability])) {
            return (bool)$this->capabilities[$capability];
        }

        return false;
    }

    /**
     * Whether this platform supports automatic identities
     *
     * @return bool
     */
    public function supportsAutomaticIdentities()
    {
        return $this->has(self::AUTO_IDENTITY);
    }
}