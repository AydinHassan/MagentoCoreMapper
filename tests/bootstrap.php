<?php
$baseDir = dirname(__DIR__);
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('AydinHassan\\MagentoCoreMapper', array($baseDir . '/src/', $baseDir . '/tests/'));
$loader->register();