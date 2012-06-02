<?php
define('LIB', __DIR__ . '/../lib');

spl_autoload_register(function ($class) {
    require strtr($class, '\\', '/') . '.php';
});
