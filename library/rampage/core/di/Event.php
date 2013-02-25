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
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\di;

use Zend\EventManager\Event as DefaultEvent;
use rampage\core\exception\InvalidArgumentException;
use rampage\core\ObjectManagerInterface;

/**
 * Di Event
 */
class Event extends DefaultEvent
{
    /**
     * Allowed class names
     */
    const CLASS_NAME_REGEX = '~^[a-z][a-z0-9_]*((\\\\|.)[a-z][a-z0-9_]*)*$~i';

    /**
     * Object manager
     *
     * @var \rampage\core\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Class name
     *
     * @var string
     */
    protected $className = null;

    /**
     * Di event
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Set the class name
     *
     * @param string $class
     * @throws InvalidArgumentException
     * @return \rampage\core\di\Event
     */
    public function setClassName($class)
    {
        if (!preg_match(self::CLASS_NAME_REGEX, $class)) {
            throw new InvalidArgumentException('Invalid class name: ' . $class);
        }

        $this->className = $class;
        return $this;
    }

    /**
     * The class name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}