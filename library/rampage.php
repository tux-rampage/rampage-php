<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */


if (!class_exists('Zend\Loader\AutoloaderFactory')) {
    require_once 'Zend/Loader/AutoloaderFactory.php';
}

$autoloadConfig = [
    'Zend\Loader\StandardAutoloader' => [
        'autoregister_zf' => true,
        'fallback_autoloader' => true,
        'namespaces' => [
            'rampage\\' => __DIR__ . '/rampage',
        ]
    ],
];

Zend\Loader\AutoloaderFactory::factory($autoloadConfig);
unset($autoloadConfig);
