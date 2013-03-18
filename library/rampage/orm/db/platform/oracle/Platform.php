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

namespace rampage\orm\db\platform\oracle;

use rampage\orm\db\platform\Platform as DefaultPlatform;
use rampage\orm\db\platform\PlatformCapabilities;
use rampage\orm\db\platform\SequenceSupportInterface;
use Zend\Db\Adapter\Adapter;
use rampage\orm\exception\RuntimeException;

/**
 * Oracle specific platform implementation
 */
class Platform extends DefaultPlatform implements SequenceSupportInterface
{
    /**
     * Sequence names by entity
     *
     * @var array
     */
    protected $sequenceNames = array();

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\Platform::initCapabilities()
     */
    protected function createCapabilities()
    {
        return new PlatformCapabilities();
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\Platform::formatIdentifier()
     */
    public function formatIdentifier($identifier)
    {
        return strtoupper($identifier);
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\Platform::createDDLRenderer()
     */
    protected function createDDLRenderer()
    {
        return new DDLRenderer($this);
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\Platform::createFieldMapper()
     */
    protected function createFieldMapper($entity)
    {
        return new FieldMapper();
    }

    /**
     * Returns the sequence name for the given entity type
     *
     * @param string $entityType
     * @return string
     */
    public function getSequenceName($entityType)
    {
        if (isset($this->sequenceNames[$entityType])) {
            return $this->sequenceNames[$entityType];
        }

        $sequence = $this->getConfig()->getSequenceName($this, $entityType);
        if (!$sequence) {
            $sequence = $this->getTable($entityType) . '_SEQ';
        }

        $this->sequenceNames[$entityType] = $sequence;
        return $sequence;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\db\platform\SequenceSupportInterface::fetchNextSequenceId()
     */
    public function fetchNextSequenceId(Adapter $adapter, $entityType)
    {
        $sequence = $this->getSequenceName($entityType);
        $platform = $this->getAdapterPlatform();

        $sql = "SELECT {$platform->quoteIdentifier($sequence)}.NEXTVAL AS NEXTVAL FROM DUAL";
        $result = $adapter->query($sql, Adapter::QUERY_MODE_EXECUTE)->current();

        if (!isset($result['NEXTVAL'])) {
            throw new RuntimeException(sprintf('Failed to fetch next value from sequence "%s"', $sequence));
        }

        return $result['NEXTVAL'];
    }
}