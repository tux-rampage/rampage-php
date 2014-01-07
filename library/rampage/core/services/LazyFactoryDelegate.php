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

namespace rampage\core\services;

use Zend\ServiceManager\FactoryInterface;
use rampage\core\exception\LogicException;
use rampage\core\exception\RuntimeException;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Code\Reflection\ClassReflection;

/**
 * Create the factory class on demand to allow lazy loading it with construction parameters
 */
class LazyFactoryDelegate implements FactoryInterface
{
    /**
     * @var string
     */
    protected $factoryClass = null;

    /**
     * @var array
     */
    protected $args = array();

    /**
     * @var FactoryInterface
     */
    private $factory = null;

    /**
     * @param string $factoryClass
     * @param array $args
     */
    public function __construct($factoryClass, $args = null)
    {
        $this->factoryClass = $factoryClass;
        if (is_array($args)) {
            $this->args = array_values($args);
        }
    }

    /**
     * Create the factory on demand
     *
     * @throws RuntimeException
     * @throws LogicException
     * @return Ambigous <\Zend\ServiceManager\FactoryInterface, unknown>
     */
    protected function createFactory()
    {
        if (!class_exists($this->factoryClass)) {
            throw new RuntimeException(sprintf(
                'Failed to create an instance of non-existing factory class "%s".',
                $this->factoryClass
            ));
        }

        $class = $this->factoryClass;

        switch (count($this->args)) {
            case 0:
                $factory = new $class();
                break;

            case 1:
                $factory = new $class($this->args[0]);
                break;

            case 2:
                $factory = new $class($this->args[0], $this->args[1]);
                break;

            case 3:
                $factory = new $class($this->args[0], $this->args[1], $this->args[2]);
                break;

            case 4:
                $factory = new $class($this->args[0], $this->args[1], $this->args[2], $this->args[3]);
                break;

            default:
                $reflection = new ClassReflection($class);
                $factory = $reflection->newInstanceArgs($this->args);
                break;
        }

        if (!$factory instanceof FactoryInterface) {
            throw new LogicException(sprintf(
                'Invalid service factory. Factory is expected to be an instance of %s, %s given.',
                'Zend\ServiceManager\FactoryInterface',
                get_class($factory)
            ));
        }

        return $factory;
    }

    /**
     * @return FactoryInterface
     */
    protected function getFactory()
    {
        if (!$this->factory) {
            $this->factory = $this->createFactory();
        }

        return $this->factory;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this->getFactory()->createService($serviceLocator);
    }
}
