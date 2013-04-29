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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm\metadata;

/**
 * Metadata definition interface
 */
interface DefinitionInterface
{
    /**
     * Check if there is a definition available fo the given entity
     *
     * @param string $name
     * @return bool
     */
    public function hasEntityDefintion($name);

    /**
     * Load the given entity metadata into the given metadata object
     *
     * @param string $name
     * @param Metadata $metadata
     * @param Entity $entity The existing entity definition
     * @return self
     */
    public function loadEntityDefintion($name, Metadata $metadata, Entity $entity = null);
}
