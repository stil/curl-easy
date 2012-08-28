<?php
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(__DIR__ . '/../src'));

spl_autoload_register(function ($class) {
    require strtr($class, '\\', '/') . '.php';
});
