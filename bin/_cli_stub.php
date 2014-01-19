#!/usr/bin/env php
<?php

$application = require __DIR__ . '/../src/bootstrap.php';
$application->add(new \AydinHassan\MagentoCoreMapper\Command\SelfUpdate());
$application->run();
