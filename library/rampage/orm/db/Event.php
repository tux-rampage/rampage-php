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

namespace rampage\orm\db;

use Zend\EventManager\Event as DefaultEvent;

/**
 * Database repository event
 */
class Event extends DefaultEvent
{
    const LOAD_BEFORE = 'load.before';
    const LOAD_AFTER = 'load.after';
    const SAVE_BEFORE = 'save.before';
    const SAVE_AFTER = 'save.after';
    const DELETE_BEFORE = 'delete.before';
    const DELETE_AFTER = 'delete.after';

    /**
     * The repository that triggered this event
     *
     * @var RepositoryInterface
     */
    private $repository = null;

    /**
     * (non-PHPdoc)
     * @see \Zend\EventManager\Event::__construct()
     */
    public function __construct(RepositoryInterface $repository, $name = null, $target = null, $params = null)
    {
        $this->repository = $repository;
        parent::__construct($name, $target, $params);
    }

    /**
     * Check repository type
     *
     * @param string $type
     * @return boolean
     */
    public function isRepositoryType($type)
    {
        if ($this->getRepository()->getName() == $type) {
            return true;
        }

        $class = strtr($type, '.', '\\');
        $result = ($this->getRepository() instanceof $class);

        return $result;
    }

    /**
     * Returns the repository responsible for this event
     *
     * @return \rampage\orm\db\RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
