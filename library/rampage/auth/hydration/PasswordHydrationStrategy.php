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

namespace rampage\auth\hydration;

use rampage\auth\PasswordStrategy;
use rampage\auth\PasswordStrategyInterface;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Credential hydration strategy
 *
 * This will ensure that passwords are crypted before being extracted
 */
class PasswordHydrationStrategy implements StrategyInterface
{
    /**
     * @var PasswordStrategyInterface
     */
    protected $passwordStrategy = null;

    /**
     * @param PasswordStrategyInterface $passwordStrategy
     */
    public function __construct(PasswordStrategyInterface $passwordStrategy)
    {
        $this->passwordStrategy = $passwordStrategy? : new PasswordStrategy();
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Stdlib\Hydrator\Strategy\StrategyInterface::extract()
     */
    public function extract($value, $object = null)
    {
        return $this->passwordStrategy->createPasswordHash($value);
    }

	/**
     * {@inheritdoc}
     * @see \Zend\Stdlib\Hydrator\Strategy\StrategyInterface::hydrate()
     */
    public function hydrate($value, $data = null)
    {
        return $value;
    }
}
