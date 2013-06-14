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
 * @package   rampage.simpleorm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\simpleorm;

use rampage\db\Adapter;
use rampage\db\metadata\Metadata as DatabaseMetadata;

/**
 * Entity manager
 */
class EntityManager
{
    /**
     * @var \rampage\db\Adapter
     */
    private $adapter = null;

    /**
     * @var \rampage\db\metadata\Metadata
     */
    private $dbMetadata = null;

    /**
     * @var metadata\Metadata
     */
    private $metadata = null;

    /**
     * @var array
     */
    private $identifierStrategies = array();

    /**
     * @var
     */
    private $typeMap = null;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->adapter = new Adapter($config->getAdapterOptions());
        $this->typeMap = new TypeMap();
        $this->metadata = new metadata\Metadata($this);
    }

    /**
     * @param Adapter $adapter
     * @return self
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return \rampage\db\Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param DatabaseMetadata $metadata
     */
    public function setDbMetadata(DatabaseMetadata $metadata)
    {
        $this->dbMetadata = $metadata;
        return $this;
    }

    /**
     * @return \rampage\db\metadata\Metadata
     */
    public function getDbMetadata()
    {
        if ($this->dbMetadata) {
            return $this->dbMetadata;
        }

        $metadata = new DatabaseMetadata($this->getAdapter());
        $this->setDbMetadata($metadata);

        return $metadata;
    }

    /**
     * Returns the type mapping
     *
     * @return \rampage\simpleorm\TypeMap
     */
    public function getTypeMap()
    {
        return $this->typeMap;
    }

    /**
     * @return \rampage\simpleorm\metadata\Metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}