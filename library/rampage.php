<?php

if (!class_exists('Zend\Loader\AutoloaderFactory')) {
    require_once 'Zend/Loader/AutoloaderFactory.php';
}

$autoloadConfig = array(
    'Zend\Loader\StandardAutoloader' => array(
        'autoregister_zf' => true,
        'fallback_autoloader' => true
    ),
);

if (is_readable(__DIR__ . '/_classmap.php')) {
    $autoloadConfig['Zend\Loader\ClassMapAutoloader'] = array(
        require_once __DIR__ . '/_classmap.php'
    );
}

Zend\Loader\AutoloaderFactory::factory($autoloadConfig);
unset($autoloadConfig);
