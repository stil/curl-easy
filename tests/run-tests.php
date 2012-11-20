<?php
$loader = require __DIR__ . '/../vendor/autoload.php';

define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');
PHPUnit_TextUI_Command::main();