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
 * Reference information
 */
class Reference
{
    const TYPE_COLLECTION = 'collection';
    const TYPE_ENTITY = 'entity';

    /**
     * @var string
     */
    private $referencedEntity = null;

    /**
     * @var string
     */
    private $type = self::TYPE_COLLECTION;

    /**
     * @var string|\rampage\simpleorm\hydration\ReferenceStrategyInterface
     */
    private $strategy = null;

    /**
     * @param string $referencedEntity
     * @param string $type
     */
    public function __construct($referencedEntity, $type = null)
    {
        $this->referencedEntity = $referencedEntity;

        if ($type !== null) {
            if (in_array($type, array(self::TYPE_COLLECTION, self::TYPE_ENTITY))) {
                $this->type = $type;
            }
        }
    }

    /**
     * @param string $strategy
     * @return \rampage\simpleorm\metadata\Reference
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * @return string
     */
    public function getReferencedEntity()
    {
        return $this->referencedEntity;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return \rampage\simpleorm\hydration\ReferenceStrategyInterface
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * @param array $fields
     * @return self
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param array $data
     * @return \rampage\simpleorm\metadata\Reference
     */
    public static function factory(array $data)
    {
        $name = $data['referenced_entity'];
        $type = (isset($data['type']))? $data['type'] : null;
        $reference = new self($name, $type);

        if (isset($data['strategy'])) {
            $reference->setStrategy($data['strategy']);
        }

        return $reference;
    }
}